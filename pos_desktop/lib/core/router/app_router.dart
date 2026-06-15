import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../features/auth/screens/pin_login_screen.dart';
import '../../features/auth/screens/terminal_setup_screen.dart';
import '../../features/pos/screens/pos_screen.dart';
import '../../features/shift/screens/open_shift_screen.dart';
import '../../features/shift/screens/close_shift_screen.dart';
import '../../features/shift/screens/shift_report_screen.dart';
import '../providers/providers.dart';

final appRouterProvider = Provider<GoRouter>((ref) {
  final authState = ref.watch(authStateProvider);
  final session = ref.watch(posSessionProvider);

  return GoRouter(
    initialLocation: '/login',
    redirect: (context, state) {
      final isLoggedIn = authState.isAuthenticated;
      final isLoginPage = state.matchedLocation == '/login';
      final isSetupPage = state.matchedLocation == '/setup';

      if (!isLoggedIn && !isLoginPage && !isSetupPage) {
        return '/login';
      }

      if (isLoggedIn && isLoginPage) {
        if (session == null) return '/setup';
        if (!session.isShiftOpen) return '/shift/open';
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
