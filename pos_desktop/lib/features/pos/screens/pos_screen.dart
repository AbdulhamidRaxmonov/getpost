import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:window_manager/window_manager.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';
import '../models/product_model.dart';
import '../providers/pos_provider.dart';
import '../widgets/cart_panel.dart';
import '../widgets/product_grid.dart';
import '../widgets/category_bar.dart';
import '../widgets/pos_title_bar.dart';
import '../widgets/search_bar_widget.dart';
import '../../payment/screens/payment_screen.dart';

class PosScreen extends ConsumerStatefulWidget {
  const PosScreen({super.key});

  @override
  ConsumerState<PosScreen> createState() => _PosScreenState();
}

class _PosScreenState extends ConsumerState<PosScreen> with WindowListener {
  final FocusNode _searchFocus = FocusNode();
  final TextEditingController _searchController = TextEditingController();
  int _activeCheckTab = 0;
  final List<Map<String, dynamic>> _checks = [
    {'id': 0, 'label': 'Chek  № 1', 'active': true},
  ];

  @override
  void initState() {
    super.initState();
    windowManager.addListener(this);
    ServicesBinding.instance.keyboard.addHandler(_handleKeyEvent);
  }

  @override
  void dispose() {
    windowManager.removeListener(this);
    ServicesBinding.instance.keyboard.removeHandler(_handleKeyEvent);
    _searchFocus.dispose();
    _searchController.dispose();
    super.dispose();
  }

  bool _handleKeyEvent(KeyEvent event) {
    if (event is KeyDownEvent) {
      // F7 = Plastik
      if (event.logicalKey == LogicalKeyboardKey.f7) {
        _openPayment('card');
        return true;
      }
      // F8 = Bo'lish
      if (event.logicalKey == LogicalKeyboardKey.f8) {
        _openPayment('cash');
        return true;
      }
      // F9 = Humo
      if (event.logicalKey == LogicalKeyboardKey.f9) {
        _openPayment('humo');
        return true;
      }
      // F6 = Naqd
      if (event.logicalKey == LogicalKeyboardKey.f6) {
        _openPayment('cash');
        return true;
      }
      // Delete = Remove selected item
      if (event.logicalKey == LogicalKeyboardKey.delete) {
        final cart = ref.read(cartProvider);
        if (cart.selectedIndex != null) {
          ref.read(cartProvider.notifier).removeItem(cart.selectedIndex!);
        }
        return true;
      }
      // + = Increment
      if (event.logicalKey == LogicalKeyboardKey.add ||
          event.logicalKey == LogicalKeyboardKey.numpadAdd) {
        ref.read(cartProvider.notifier).incrementSelected();
        return true;
      }
      // - = Decrement
      if (event.logicalKey == LogicalKeyboardKey.minus ||
          event.logicalKey == LogicalKeyboardKey.numpadSubtract) {
        ref.read(cartProvider.notifier).decrementSelected();
        return true;
      }
    }
    return false;
  }

  void _openPayment(String defaultMethod) {
    final cart = ref.read(cartProvider);
    if (cart.isEmpty) {
      _showSnack('Savat bo\'sh!');
      return;
    }
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (_) => PaymentScreen(defaultPaymentMethod: defaultMethod),
    );
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(content: Text(msg), duration: const Duration(seconds: 2)),
    );
  }

  void _addNewCheck() {
    setState(() {
      final newId = _checks.length;
      _checks.add({'id': newId, 'label': 'Chek  № ${newId + 1}', 'active': true});
      _activeCheckTab = newId;
    });
    ref.read(cartProvider.notifier).clear(nextCheckNumber: _checks.length);
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);
    final session = ref.watch(posSessionProvider);
    final authState = ref.watch(authStateProvider);

    return Scaffold(
      backgroundColor: Colors.white,
      body: Column(
        children: [
          // ── Title Bar ──────────────────────────────────────
          PosTitleBar(
            cashierName: authState.user?['name'] ?? 'Kassir',
            branchName: session?.branchName ?? '',
            terminalName: session?.terminalName ?? 'Kassa',
            shiftId: session?.shiftId,
            onLogout: () {
              ref.read(authStateProvider.notifier).logout();
              context.go('/login');
            },
            onCloseShift: () => context.push('/shift/close'),
          ),

          // ── Main Body ──────────────────────────────────────
          Expanded(
            child: Row(
              children: [
                // ── Left: Product Panel ──────────────────────
                Expanded(
                  flex: 7,
                  child: Column(
                    children: [
                      // Check tabs bar
                      _buildCheckTabs(),

                      // Search bar
                      SearchBarWidget(
                        controller: _searchController,
                        focusNode: _searchFocus,
                        onChanged: (v) => ref.read(searchQueryProvider.notifier).state = v,
                      ),

                      // Category filter
                      const CategoryBar(),

                      // Product grid + cart table
                      const Expanded(child: ProductGrid()),
                    ],
                  ),
                ),

                // ── Right: Cart Panel ────────────────────────
                SizedBox(
                  width: 310,
                  child: CartPanel(
                    onPayCash: () => _openPayment('cash'),
                    onPayCard: () => _openPayment('card'),
                    onPayClick: () => _openPayment('click'),
                    onPayPayme: () => _openPayment('payme'),
                    onPayHumo: () => _openPayment('humo'),
                    onPayUzcard: () => _openPayment('uzcard'),
                    onPayDebt: () => _openPayment('debt'),
                    onPrechek: () => _openPayment('cash'),
                  ),
                ),
              ],
            ),
          ),

          // ── Status Bar ──────────────────────────────────────
          _buildStatusBar(session, authState),
        ],
      ),
    );
  }

  Widget _buildCheckTabs() {
    return Container(
      height: 36,
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Row(
        children: [
          Expanded(
            child: SingleChildScrollView(
              scrollDirection: Axis.horizontal,
              child: Row(
                children: [
                  // "Tanlanganlar" button
                  _TabButton(
                    label: 'Tanlanganlar',
                    isActive: false,
                    onTap: () {},
                  ),
                  ...List.generate(_checks.length, (i) => _TabButton(
                    label: _checks[i]['label'],
                    isActive: i == _activeCheckTab,
                    onTap: () => setState(() => _activeCheckTab = i),
                    hasCheck: true,
                  )),
                  // New check button
                  InkWell(
                    onTap: _addNewCheck,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                      child: const Row(
                        children: [
                          Icon(Icons.add, size: 14, color: Color(0xFF6B7280)),
                          SizedBox(width: 4),
                          Text('Yangi chek', style: TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                        ],
                      ),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildStatusBar(PosSession? session, AuthState authState) {
    return Container(
      height: 28,
      color: const Color(0xFF1E3A5F),
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: Row(
        children: [
          _StatusItem(
            label: 'ИД',
            value: '#${session?.shiftId ?? '—'}',
          ),
          const _StatusDivider(),
          _StatusItem(
            label: 'Hodim',
            value: authState.user?['name'] ?? '—',
          ),
          const _StatusDivider(),
          _StatusItem(
            label: 'Filial',
            value: session?.branchName ?? '—',
          ),
          const _StatusDivider(),
          _StatusItem(
            label: 'POS',
            value: session?.terminalName ?? '—',
          ),
          const _StatusDivider(),
          _StatusItem(
            label: 'Sinxronizatsiya',
            value: _formatTime(DateTime.now()),
          ),
          const Spacer(),
          // Currency rate
          const Text(
            '1 USD = 0 so\'m',
            style: TextStyle(fontSize: 10, color: Colors.white60),
          ),
        ],
      ),
    );
  }

  String _formatTime(DateTime dt) {
    return '${dt.hour.toString().padLeft(2, '0')}:'
        '${dt.minute.toString().padLeft(2, '0')}:'
        '${dt.second.toString().padLeft(2, '0')}';
  }
}

class _TabButton extends StatelessWidget {
  final String label;
  final bool isActive;
  final VoidCallback onTap;
  final bool hasCheck;

  const _TabButton({
    required this.label,
    required this.isActive,
    required this.onTap,
    this.hasCheck = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
        decoration: BoxDecoration(
          border: Border(
            bottom: BorderSide(
              color: isActive ? AppTheme.primaryBlue : Colors.transparent,
              width: 2,
            ),
          ),
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (hasCheck)
              const Icon(Icons.check, size: 13, color: Color(0xFF10B981)),
            if (hasCheck) const SizedBox(width: 4),
            Text(
              label,
              style: TextStyle(
                fontSize: 12,
                fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
                color: isActive ? AppTheme.primaryBlue : const Color(0xFF374151),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _StatusItem extends StatelessWidget {
  final String label;
  final String value;
  const _StatusItem({required this.label, required this.value});

  @override
  Widget build(BuildContext context) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Text('$label: ', style: const TextStyle(fontSize: 10, color: Colors.white54)),
        Text(value, style: const TextStyle(fontSize: 10, color: Colors.white, fontWeight: FontWeight.w500)),
      ],
    );
  }
}

class _StatusDivider extends StatelessWidget {
  const _StatusDivider();
  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 8),
      width: 1,
      height: 14,
      color: Colors.white24,
    );
  }
}
