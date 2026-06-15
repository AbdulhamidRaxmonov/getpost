import 'package:flutter/foundation.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/screens/pin_login_screen.dart';
import '../../features/auth/screens/terminal_setup_screen.dart';
import '../../features/pos/screens/pos_screen.dart';
import '../../features/shift/screens/open_shift_screen.dart';
import '../../features/shift/screens/close_shift_screen.dart';
import '../../features/shift/screens/shift_report_screen.dart';
import '../providers/providers.dart';

// ChangeNotifier — GoRouter refreshListenable uchun
class _RouterNotifier extends ChangeNotifier {
  final Ref _ref;

  _RouterNotifier(this._ref) {
    // authState o'zgarganda GoRouter redirect qayta ishlaydi
    _ref.listen<AuthState>(authStateProvider, (_, __) {
      notifyListeners();
    });
    _ref.listen<PosSession?>(posSessionProvider, (_, __) {
      notifyListeners();
    });
  }

  bool get isLoggedIn =>
      _ref.read(authStateProvider).isAuthenticated;

  bool get hasSession =>
      _ref.read(posSessionProvider) != null;

  bool get isShiftOpen =>
      _ref.read(posSessionProvider)?.isShiftOpen ?? false;
}

final appRouterProvider = Provider<GoRouter>((ref) {
  final notifier = _RouterNotifier(ref);

  return GoRouter(
    initialLocation: '/login',
    refreshListenable: notifier,
    redirect: (context, state) {
      final isLoggedIn = notifier.isLoggedIn;
      final hasSession = notifier.hasSession;
      final isShiftOpen = notifier.isShiftOpen;

      final path = state.matchedLocation;
      final isLoginPage = path == '/login';
      final isSetupPage = path == '/setup';

      // Login qilinmagan
      if (!isLoggedIn) {
        if (!isLoginPage && !isSetupPage) return '/login';
        return null;
      }

      // Login qilingan va login sahifasida
      if (isLoggedIn && isLoginPage) {
        if (!hasSession) return '/setup';
        if (!isShiftOpen) return '/shift/open';
        return '/pos';
      }

      return null;
    },
    routes: [
      GoRoute(
        path: '/login',
        name: 'login',
        builder: (context, state) => const PinLoginScreen(),
      ),
      GoRoute(
        path: '/setup',
        name: 'setup',
        builder: (context, state) => const TerminalSetupScreen(),
      ),
      GoRoute(
        path: '/pos',
        name: 'pos',
        builder: (context, state) => const PosScreen(),
      ),
      GoRoute(
        path: '/shift/open',
        name: 'shift-open',
        builder: (context, state) => const OpenShiftScreen(),
      ),
      GoRoute(
        path: '/shift/close',
        name: 'shift-close',
        builder: (context, state) => const CloseShiftScreen(),
      ),
      GoRoute(
        path: '/shift/report/:id',
        name: 'shift-report',
        builder: (context, state) => ShiftReportScreen(
          shiftId: int.parse(state.pathParameters['id']!),
        ),
      ),
    ],
  );
});
