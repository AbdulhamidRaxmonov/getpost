import 'dart:convert';

import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';

// ─── SharedPreferences ───────────────────────────────────────────────────────
final sharedPreferencesProvider = Provider<SharedPreferences>(
  (ref) => throw UnimplementedError(),
);

// ─── Services ─────────────────────────────────────────────────────────────────
final apiServiceProvider = Provider<ApiService>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return ApiService(prefs: prefs);
});

final authServiceProvider = Provider<AuthService>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  final api = ref.watch(apiServiceProvider);
  return AuthService(prefs: prefs, api: api);
});

final storageServiceProvider = Provider<StorageService>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return StorageService(prefs: prefs);
});

// ─── Session ──────────────────────────────────────────────────────────────────
final posSessionProvider =
    StateNotifierProvider<PosSessionNotifier, PosSession?>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return PosSessionNotifier(prefs);
});

// ─── Auth ─────────────────────────────────────────────────────────────────────
final authStateProvider =
    StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final authService = ref.watch(authServiceProvider);
  return AuthNotifier(authService, ref);
});

// ═══════════════════════════════════════════════════════════════════════════════
// AuthState
// ═══════════════════════════════════════════════════════════════════════════════
class AuthState {
  final bool isLoading;
  final bool isAuthenticated;
  final String? token;
  final Map<String, dynamic>? user;
  final String? error;

  const AuthState({
    this.isLoading = false,
    this.isAuthenticated = false,
    this.token,
    this.user,
    this.error,
  });

  AuthState copyWith({
    bool? isLoading,
    bool? isAuthenticated,
    String? token,
    Map<String, dynamic>? user,
    String? error,
  }) {
    return AuthState(
      isLoading: isLoading ?? this.isLoading,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      token: token ?? this.token,
      user: user ?? this.user,
      error: error,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;
  final Ref _ref;

  AuthNotifier(this._authService, this._ref) : super(const AuthState()) {
    _init();
  }

  void _init() {
    final token = _authService.getToken();
    final user = _authService.getUser();
    if (token != null && user != null) {
      state = state.copyWith(
        isAuthenticated: true,
        token: token,
        user: user,
      );
    }
  }

  Future<bool> pinLogin({
    required int terminalId,
    required String pin,
  }) async {
    state = state.copyWith(isLoading: true, error: null);

    try {
      final result = await _authService.pinLogin(
        terminalId: terminalId,
        pin: pin,
      );

      if (result == null) {
        state = state.copyWith(
          isLoading: false,
          error: 'PIN kod noto\'g\'ri',
        );
        return false;
      }

      // Session ni yangilash — ref orqali (circular dependency yo'q)
      _ref.read(posSessionProvider.notifier).setSessionFromLoginResult(result);

      state = state.copyWith(
        isLoading: false,
        isAuthenticated: true,
        token: result['token'] as String?,
        user: result['user'] as Map<String, dynamic>?,
      );

      return true;
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        error: e.toString(),
      );
      return false;
    }
  }

  void logout() {
    _authService.logout();
    state = const AuthState();
  }
}

// ═══════════════════════════════════════════════════════════════════════════════
// PosSession
// ═══════════════════════════════════════════════════════════════════════════════
class PosSession {
  final int terminalId;
  final String terminalName;
  final int branchId;
  final String branchName;
  final int organizationId;
  final String organizationName;
  final int? shiftId;
  final bool isShiftOpen;
  final DateTime? shiftOpenedAt;
  final double openingCash;

  const PosSession({
    required this.terminalId,
    required this.terminalName,
    required this.branchId,
    required this.branchName,
    required this.organizationId,
    required this.organizationName,
    this.shiftId,
    this.isShiftOpen = false,
    this.shiftOpenedAt,
    this.openingCash = 0,
  });

  PosSession copyWith({
    int? shiftId,
    bool? isShiftOpen,
    DateTime? shiftOpenedAt,
    double? openingCash,
  }) {
    return PosSession(
      terminalId: terminalId,
      terminalName: terminalName,
      branchId: branchId,
      branchName: branchName,
      organizationId: organizationId,
      organizationName: organizationName,
      shiftId: shiftId ?? this.shiftId,
      isShiftOpen: isShiftOpen ?? this.isShiftOpen,
      shiftOpenedAt: shiftOpenedAt ?? this.shiftOpenedAt,
      openingCash: openingCash ?? this.openingCash,
    );
  }
}

class PosSessionNotifier extends StateNotifier<PosSession?> {
  final SharedPreferences _prefs;

  PosSessionNotifier(this._prefs) : super(null) {
    _loadSession();
  }

  void _loadSession() {
    final terminalId = _prefs.getInt('terminal_id');
    if (terminalId == null) return;

    final branchId = _prefs.getInt('branch_id') ?? 1;
    final orgId = _prefs.getInt('organization_id') ?? 1;

    state = PosSession(
      terminalId: terminalId,
      terminalName: _prefs.getString('terminal_name') ?? 'Kassa',
      branchId: branchId,
      branchName: _prefs.getString('branch_name') ?? 'Filial',
      organizationId: orgId,
      organizationName: _prefs.getString('organization_name') ?? '',
      shiftId: _prefs.getInt('shift_id'),
      isShiftOpen: _prefs.getBool('shift_open') ?? false,
      openingCash: _prefs.getDouble('opening_cash') ?? 0,
    );
  }

  /// Laravel PIN login javobidan session yaratish
  void setSessionFromLoginResult(Map<String, dynamic> result) {
    final terminal = result['terminal'];
    if (terminal == null) return;

    final branch = terminal['branch'] ?? {};
    final org = branch['organization'] ?? {};

    final terminalId = terminal['id'] as int? ?? state?.terminalId ?? 1;
    final terminalName = terminal['name'] as String? ?? 'Kassa';
    final branchId = branch['id'] as int? ?? state?.branchId ?? 1;
    final branchName = branch['name'] as String? ?? '';
    final orgId = org['id'] as int? ?? state?.organizationId ?? 1;
    final orgName = org['name'] as String? ?? '';

    final currentShift = result['current_shift'];
    final shiftId = currentShift?['id'] as int?;
    final isShiftOpen = currentShift != null &&
        currentShift['status'] == 'open';

    // SharedPreferences ga saqlash
    _prefs.setInt('terminal_id', terminalId);
    _prefs.setString('terminal_name', terminalName);
    _prefs.setInt('branch_id', branchId);
    _prefs.setString('branch_name', branchName);
    _prefs.setInt('organization_id', orgId);
    _prefs.setString('organization_name', orgName);
    _prefs.setBool('shift_open', isShiftOpen);
    if (shiftId != null) {
      _prefs.setInt('shift_id', shiftId);
    } else {
      _prefs.remove('shift_id');
    }

    state = PosSession(
      terminalId: terminalId,
      terminalName: terminalName,
      branchId: branchId,
      branchName: branchName,
      organizationId: orgId,
      organizationName: orgName,
      shiftId: shiftId,
      isShiftOpen: isShiftOpen,
    );
  }

  /// Terminal setup ekranidan session yaratish
  void setSession({
    required int terminalId,
    required String terminalName,
    required int branchId,
    required String branchName,
    required int organizationId,
    required String organizationName,
  }) {
    _prefs.setInt('terminal_id', terminalId);
    _prefs.setString('terminal_name', terminalName);
    _prefs.setInt('branch_id', branchId);
    _prefs.setString('branch_name', branchName);
    _prefs.setInt('organization_id', organizationId);
    _prefs.setString('organization_name', organizationName);

    state = PosSession(
      terminalId: terminalId,
      terminalName: terminalName,
      branchId: branchId,
      branchName: branchName,
      organizationId: organizationId,
      organizationName: organizationName,
    );
  }

  void openShift({required int shiftId, required double openingCash}) {
    _prefs.setInt('shift_id', shiftId);
    _prefs.setBool('shift_open', true);
    _prefs.setDouble('opening_cash', openingCash);
    state = state?.copyWith(
      shiftId: shiftId,
      isShiftOpen: true,
      shiftOpenedAt: DateTime.now(),
      openingCash: openingCash,
    );
  }

  void closeShift() {
    _prefs.remove('shift_id');
    _prefs.setBool('shift_open', false);
    state = state?.copyWith(isShiftOpen: false, shiftId: null);
  }
}
