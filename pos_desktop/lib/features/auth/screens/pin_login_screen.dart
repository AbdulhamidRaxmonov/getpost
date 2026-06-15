import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:window_manager/window_manager.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';
class PinLoginScreen extends ConsumerStatefulWidget {
  const PinLoginScreen({super.key});

  @override
  ConsumerState<PinLoginScreen> createState() => _PinLoginScreenState();
}

class _PinLoginScreenState extends ConsumerState<PinLoginScreen>
    with TickerProviderStateMixin {
  String _pin = '';
  static const int _maxPin = 4;
  bool _isLoading = false;
  String _errorText = '';
  late Timer _clockTimer;
  String _currentTime = '';
  String _currentDate = '';

  late AnimationController _shakeController;
  late Animation<double> _shakeAnimation;

  @override
  void initState() {
    super.initState();
    _updateClock();
    _clockTimer = Timer.periodic(const Duration(seconds: 1), (_) => _updateClock());

    _shakeController = AnimationController(
      duration: const Duration(milliseconds: 500),
      vsync: this,
    );
    _shakeAnimation = Tween<double>(begin: 0, end: 1).animate(
      CurvedAnimation(parent: _shakeController, curve: Curves.elasticIn),
    );

    // Focus keyboard
    ServicesBinding.instance.keyboard.addHandler(_handleKeyEvent);
  }

  @override
  void dispose() {
    _clockTimer.cancel();
    _shakeController.dispose();
    ServicesBinding.instance.keyboard.removeHandler(_handleKeyEvent);
    super.dispose();
  }

  bool _handleKeyEvent(KeyEvent event) {
    if (event is KeyDownEvent) {
      if (event.logicalKey == LogicalKeyboardKey.backspace) {
        _onDelete();
        return true;
      }
      final digit = _getDigitFromKey(event.logicalKey);
      if (digit != null) {
        _onNumber(digit.toString());
        return true;
      }
    }
    return false;
  }

  int? _getDigitFromKey(LogicalKeyboardKey key) {
    final digits = <LogicalKeyboardKey, int>{
      LogicalKeyboardKey.digit0: 0,
      LogicalKeyboardKey.digit1: 1,
      LogicalKeyboardKey.digit2: 2,
      LogicalKeyboardKey.digit3: 3,
      LogicalKeyboardKey.digit4: 4,
      LogicalKeyboardKey.digit5: 5,
      LogicalKeyboardKey.digit6: 6,
      LogicalKeyboardKey.digit7: 7,
      LogicalKeyboardKey.digit8: 8,
      LogicalKeyboardKey.digit9: 9,
      LogicalKeyboardKey.numpad0: 0,
      LogicalKeyboardKey.numpad1: 1,
      LogicalKeyboardKey.numpad2: 2,
      LogicalKeyboardKey.numpad3: 3,
      LogicalKeyboardKey.numpad4: 4,
      LogicalKeyboardKey.numpad5: 5,
      LogicalKeyboardKey.numpad6: 6,
      LogicalKeyboardKey.numpad7: 7,
      LogicalKeyboardKey.numpad8: 8,
      LogicalKeyboardKey.numpad9: 9,
    };
    return digits[key];
  }

  void _updateClock() {
    final now = DateTime.now();
    final days = ['Dush', 'Sesh', 'Chor', 'Pay', 'Ju', 'Shan', 'Yak'];
    final months = ['Yanvar', 'Fevral', 'Mart', 'Aprel', 'May', 'Iyun',
        'Iyul', 'Avgust', 'Sentabr', 'Oktabr', 'Noyabr', 'Dekabr'];
    setState(() {
      _currentTime = '${now.hour.toString().padLeft(2, '0')}:'
          '${now.minute.toString().padLeft(2, '0')}:'
          '${now.second.toString().padLeft(2, '0')}';
      _currentDate = '${now.day.toString().padLeft(2, '0')}.'
          '${months[now.month - 1]} ${now.year}, ${days[now.weekday - 1]}';
    });
  }

  void _onNumber(String num) {
    if (_pin.length < _maxPin) {
      setState(() {
        _pin += num;
        _errorText = '';
      });
      if (_pin.length == _maxPin) {
        _submitPin();
      }
    }
  }

  void _onDelete() {
    if (_pin.isNotEmpty) {
      setState(() {
        _pin = _pin.substring(0, _pin.length - 1);
        _errorText = '';
      });
    }
  }

  void _onClear() {
    setState(() {
      _pin = '';
      _errorText = '';
    });
  }

  Future<void> _submitPin() async {
    final session = ref.read(posSessionProvider);
    if (session == null) {
      context.go('/setup');
      return;
    }

    setState(() => _isLoading = true);

    final success = await ref.read(authStateProvider.notifier).pinLogin(
      terminalId: session.terminalId,
      pin: _pin,
    );

    if (!mounted) return;

    setState(() => _isLoading = false);

    if (success) {
      if (!session.isShiftOpen) {
        context.go('/shift/open');
      } else {
        context.go('/pos');
      }
    } else {
      setState(() {
        _errorText = 'PIN kod noto\'g\'ri!';
        _pin = '';
      });
      _shakeController.forward(from: 0);
    }
  }

  @override
  Widget build(BuildContext context) {
    final session = ref.watch(posSessionProvider);

    return Scaffold(
      body: Stack(
        children: [
          // Background gradient - YesPOS style (dark blue)
          Container(
            decoration: const BoxDecoration(
              gradient: LinearGradient(
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
                colors: [
                  Color(0xFF0D2B5C),
                  Color(0xFF1A4080),
                  Color(0xFF0D3D73),
                ],
              ),
            ),
          ),

          // Background wave shapes (like in screenshot)
          Positioned(
            top: -100,
            right: -100,
            child: Container(
              width: 500,
              height: 500,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.04),
              ),
            ),
          ),
          Positioned(
            bottom: -150,
            left: -100,
            child: Container(
              width: 600,
              height: 600,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: Colors.white.withOpacity(0.04),
              ),
            ),
          ),

          // Title bar
          _buildTitleBar(),

          // Main content
          Center(
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                // PIN pad card
                _buildPinCard(session),
                const SizedBox(width: 0),
                // Logo/info card
                _buildInfoCard(session),
              ],
            ),
          ),

          // Clock at bottom
          Positioned(
            bottom: 40,
            left: 0,
            right: 0,
            child: Column(
              children: [
                Text(
                  _currentTime,
                  style: const TextStyle(
                    fontSize: 56,
                    fontWeight: FontWeight.bold,
                    color: Colors.white,
                    letterSpacing: 4,
                    height: 1,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  _currentDate,
                  style: TextStyle(
                    fontSize: 16,
                    color: Colors.white.withOpacity(0.7),
                    letterSpacing: 1,
                  ),
                  textAlign: TextAlign.center,
                ),
                const SizedBox(height: 4),
                Text(
                  'v-1.0.0',
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.white.withOpacity(0.4),
                  ),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildTitleBar() {
    return Positioned(
      top: 0,
      left: 0,
      right: 0,
      child: GestureDetector(
        onPanStart: (_) => windowManager.startDragging(),
        child: Container(
          height: 40,
          color: Colors.transparent,
          child: Row(
            children: [
              const SizedBox(width: 16),
              Image.asset('assets/images/logo.png', height: 20, errorBuilder: (_, __, ___) =>
                  const Icon(Icons.point_of_sale, color: Colors.white, size: 20)),
              const SizedBox(width: 8),
              const Text('YesPOS', style: TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.w600)),
              const Spacer(),
              _WindowButton(icon: Icons.minimize, onTap: () => windowManager.minimize()),
              _WindowButton(icon: Icons.crop_square, onTap: () => windowManager.maximize()),
              _WindowButton(icon: Icons.close, onTap: () => windowManager.close(), isClose: true),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildPinCard(PosSession? session) {
    return AnimatedBuilder(
      animation: _shakeAnimation,
      builder: (context, child) {
        final shake = _shakeController.isAnimating
            ? 8 * (0.5 - (_shakeAnimation.value - 0.5).abs())
            : 0.0;
        return Transform.translate(
          offset: Offset(shake, 0),
          child: child,
        );
      },
      child: Container(
        width: 280,
        padding: const EdgeInsets.all(28),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: const BorderRadius.only(
            topLeft: Radius.circular(16),
            bottomLeft: Radius.circular(16),
          ),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.3),
              blurRadius: 30,
              offset: const Offset(0, 10),
            ),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              'Parolni kiriting',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Colors.grey.shade700,
              ),
            ),
            const SizedBox(height: 20),

            // PIN dots
            _buildPinDots(),

            // Error text
            AnimatedContainer(
              duration: const Duration(milliseconds: 200),
              height: _errorText.isNotEmpty ? 28 : 0,
              child: _errorText.isNotEmpty
                  ? Text(
                      _errorText,
                      style: const TextStyle(color: AppTheme.danger, fontSize: 13),
                    )
                  : null,
            ),

            const SizedBox(height: 16),

            // Numpad
            _buildNumpad(),
          ],
        ),
      ),
    );
  }

  Widget _buildPinDots() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: List.generate(_maxPin, (i) {
        final filled = i < _pin.length;
        return AnimatedContainer(
          duration: const Duration(milliseconds: 150),
          margin: const EdgeInsets.symmetric(horizontal: 8),
          width: 14,
          height: 14,
          decoration: BoxDecoration(
            shape: BoxShape.circle,
            color: filled ? AppTheme.primaryBlue : Colors.transparent,
            border: Border.all(
              color: filled ? AppTheme.primaryBlue : Colors.grey.shade400,
              width: 2,
            ),
          ),
        );
      }),
    );
  }

  Widget _buildNumpad() {
    if (_isLoading) {
      return const SizedBox(
        height: 200,
        child: Center(
          child: CircularProgressIndicator(color: AppTheme.primaryBlue),
        ),
      );
    }

    return Column(
      children: [
        _buildNumRow(['1', '2', '3']),
        const SizedBox(height: 10),
        _buildNumRow(['4', '5', '6']),
        const SizedBox(height: 10),
        _buildNumRow(['7', '8', '9']),
        const SizedBox(height: 10),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _buildSpecialKey(icon: Icons.backspace_outlined, onTap: _onClear),
            const SizedBox(width: 10),
            _buildNumKey('0'),
            const SizedBox(width: 10),
            _buildSpecialKey(icon: Icons.backspace, onTap: _onDelete),
          ],
        ),
      ],
    );
  }

  Widget _buildNumRow(List<String> nums) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.center,
      children: nums.asMap().entries.map((e) {
        return Row(
          children: [
            if (e.key > 0) const SizedBox(width: 10),
            _buildNumKey(e.value),
          ],
        );
      }).toList(),
    );
  }

  Widget _buildNumKey(String num) {
    return _NumpadButton(
      label: num,
      onTap: () => _onNumber(num),
    );
  }

  Widget _buildSpecialKey({required IconData icon, required VoidCallback onTap}) {
    return _NumpadButton(
      icon: icon,
      onTap: onTap,
    );
  }

  Widget _buildInfoCard(PosSession? session) {
    return Container(
      width: 260,
      height: 290,
      decoration: BoxDecoration(
        color: Colors.black.withOpacity(0.3),
        borderRadius: const BorderRadius.only(
          topRight: Radius.circular(16),
          bottomRight: Radius.circular(16),
        ),
      ),
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Text(
            'YesPOS',
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: Colors.white,
              letterSpacing: 2,
            ),
          ),
          const SizedBox(height: 8),
          Text(
            'Savdo boshqaruv tizimi',
            style: TextStyle(
              fontSize: 14,
              color: Colors.white.withOpacity(0.7),
            ),
          ),
          const SizedBox(height: 24),
          if (session != null) ...[
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Column(
                children: [
                  Text(
                    session.organizationName,
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.w600,
                      fontSize: 14,
                    ),
                    textAlign: TextAlign.center,
                  ),
                  const SizedBox(height: 4),
                  Text(
                    '${session.branchName} • ${session.terminalName}',
                    style: TextStyle(
                      color: Colors.white.withOpacity(0.7),
                      fontSize: 12,
                    ),
                    textAlign: TextAlign.center,
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 24),
          Text(
            'Call-markaz: +998 (00) 123-45-67',
            style: TextStyle(
              fontSize: 12,
              color: Colors.white.withOpacity(0.5),
            ),
          ),
        ],
      ),
    );
  }
}

class _NumpadButton extends StatefulWidget {
  final String? label;
  final IconData? icon;
  final VoidCallback onTap;

  const _NumpadButton({
    this.label,
    this.icon,
    required this.onTap,
  });

  @override
  State<_NumpadButton> createState() => _NumpadButtonState();
}

class _NumpadButtonState extends State<_NumpadButton>
    with SingleTickerProviderStateMixin {
  late AnimationController _controller;
  late Animation<double> _scaleAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(
      duration: const Duration(milliseconds: 100),
      vsync: this,
    );
    _scaleAnimation = Tween<double>(begin: 1.0, end: 0.92).animate(
      CurvedAnimation(parent: _controller, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTapDown: (_) => _controller.forward(),
      onTapUp: (_) {
        _controller.reverse();
        widget.onTap();
      },
      onTapCancel: () => _controller.reverse(),
      child: ScaleTransition(
        scale: _scaleAnimation,
        child: Container(
          width: 72,
          height: 60,
          decoration: BoxDecoration(
            color: Colors.grey.shade50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade200),
            boxShadow: [
              BoxShadow(
                color: Colors.grey.withOpacity(0.1),
                blurRadius: 4,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Center(
            child: widget.label != null
                ? Text(
                    widget.label!,
                    style: const TextStyle(
                      fontSize: 22,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF1F2937),
                    ),
                  )
                : Icon(widget.icon, size: 22, color: Colors.grey.shade600),
          ),
        ),
      ),
    );
  }
}

class _WindowButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final bool isClose;

  const _WindowButton({
    required this.icon,
    required this.onTap,
    this.isClose = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        width: 46,
        height: 40,
        color: Colors.transparent,
        child: Icon(
          icon,
          color: Colors.white.withOpacity(0.7),
          size: 16,
        ),
      ),
    );
  }
}
