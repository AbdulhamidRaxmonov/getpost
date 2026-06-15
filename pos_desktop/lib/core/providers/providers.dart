import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../services/api_service.dart';
import '../services/auth_service.dart';
import '../services/storage_service.dart';

// SharedPreferences
final sharedPreferencesProvider = Provider<SharedPreferences>(
  (ref) => throw UnimplementedError(),
);

// Services
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

// Auth state
final authStateProvider = StateNotifierProvider<AuthNotifier, AuthState>((ref) {
  final authService = ref.watch(authServiceProvider);
  return AuthNotifier(authService);
});

// Session (terminal, shift, user info)
final posSessionProvider = StateNotifierProvider<PosSessionNotifier, PosSession?>((ref) {
  final prefs = ref.watch(sharedPreferencesProvider);
  return PosSessionNotifier(prefs);
});

// ========================
// Auth State
// ========================
class AuthState {
  final bool isLoading;
  final bool isAuthenticated;
  final String? token;
  final Map<String, dynamic>? user;
  final Map<String, dynamic>? terminal;
  final String? error;

  const AuthState({
    this.isLoading = false,
    this.isAuthenticated = false,
    this.token,
    this.user,
    this.terminal,
    this.error,
  });

  AuthState copyWith({
    bool? isLoading,
    bool? isAuthenticated,
    String? token,
    Map<String, dynamic>? user,
    Map<String, dynamic>? terminal,
    String? error,
  }) {
    return AuthState(
      isLoading: isLoading ?? this.isLoading,
      isAuthenticated: isAuthenticated ?? this.isAuthenticated,
      token: token ?? this.token,
      user: user ?? this.user,
      terminal: terminal ?? this.terminal,
      error: error,
    );
  }
}

class AuthNotifier extends StateNotifier<AuthState> {
  final AuthService _authService;

  AuthNotifier(this._authService) : super(const AuthState()) {
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
      if (result != null) {
        state = state.copyWith(
          isLoading: false,
          isAuthenticated: true,
          token: result['token'],
          user: result['user'],
          terminal: result['terminal'],
        );
        return true;
      }
      state = state.copyWith(isLoading: false, error: 'PIN kod noto\'g\'ri');
      return false;
    } catch (e) {
      state = state.copyWith(isLoading: false, error: e.toString());
      return false;
    }
  }

  void logout() {
    _authService.logout();
    state = const AuthState();
  }
}

// ========================
// POS Session State
// ========================
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
    final terminalName = _prefs.getString('terminal_name');
    final branchId = _prefs.getInt('branch_id');
    final branchName = _prefs.getString('branch_name');
    final orgId = _prefs.getInt('organization_id');
    final orgName = _prefs.getString('organization_name');

    if (terminalId != null && branchId != null && orgId != null) {
      state = PosSession(
        terminalId: terminalId,
        terminalName: terminalName ?? 'Kassa',
        branchId: branchId,
        branchName: branchName ?? 'Filial',
        organizationId: orgId,
        organizationName: orgName ?? '',
        shiftId: _prefs.getInt('shift_id'),
        isShiftOpen: _prefs.getBool('shift_open') ?? false,
        openingCash: _prefs.getDouble('opening_cash') ?? 0,
      );
    }
  }

  void setSession(Map<String, dynamic> terminalData) {
    final terminal = terminalData['terminal'];
    final branch = terminal['branch'];
    final org = branch['organization'];

    _prefs.setInt('terminal_id', terminal['id']);
    _prefs.setString('terminal_name', terminal['name']);
    _prefs.setInt('branch_id', branch['id']);
    _prefs.setString('branch_name', branch['name']);
    _prefs.setInt('organization_id', org['id']);
    _prefs.setString('organization_name', org['name']);

    state = PosSession(
      terminalId: terminal['id'],
      terminalName: terminal['name'],
      branchId: branch['id'],
      branchName: branch['name'],
      organizationId: org['id'],
      organizationName: org['name'],
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
    state = state?.copyWith(isShiftOpen: false, shiftId: -1);
  }
}
