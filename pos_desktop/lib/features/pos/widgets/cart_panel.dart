import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../providers/pos_provider.dart';
import '../models/product_model.dart';

class CartPanel extends ConsumerWidget {
  final VoidCallback onPayCash;
  final VoidCallback onPayCard;
  final VoidCallback onPayClick;
  final VoidCallback onPayPayme;
  final VoidCallback onPayHumo;
  final VoidCallback onPayUzcard;
  final VoidCallback onPayDebt;
  final VoidCallback onPrechek;

  const CartPanel({
    super.key,
    required this.onPayCash,
    required this.onPayCard,
    required this.onPayClick,
    required this.onPayPayme,
    required this.onPayHumo,
    required this.onPayUzcard,
    required this.onPayDebt,
    required this.onPrechek,
  });

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final cart = ref.watch(cartProvider);

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(left: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Column(
        children: [
          // ── Action buttons (O'chirish, Cheklar, Bloklash) ───
          _buildTopActions(ref, cart),

          // ── Client selector ─────────────────────────────────
          _buildClientSelector(ref, cart),

          const Spacer(),

          // ── Payment method buttons ───────────────────────────
          _buildPaymentMethods(),

          const Divider(height: 1),

          // ── Total ───────────────────────────────────────────
          _buildTotal(cart),

          // ── Prechek button ──────────────────────────────────
          _buildPrechekButton(),
        ],
      ),
    );
  }

  Widget _buildTopActions(WidgetRef ref, CartState cart) {
    return Container(
      padding: const EdgeInsets.all(8),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Row(
        children: [
          // O'chirish (red X button - rasmdagidek)
          Expanded(
            child: _ActionBtn(
              label: "O'chirish",
              icon: Icons.close,
              color: AppTheme.danger,
              onTap: () => ref.read(cartProvider.notifier).clear(),
            ),
          ),
          const SizedBox(width: 8),
          // Cheklar
          Expanded(
            child: _ActionBtn(
              label: 'Cheklar',
              icon: Icons.receipt_long_outlined,
              color: const Color(0xFF374151),
              onTap: () {},
            ),
          ),
          const SizedBox(width: 8),
          // Bloklash
          Expanded(
            child: _ActionBtn(
              label: 'Bloklash',
              icon: Icons.lock_outlined,
              color: const Color(0xFF374151),
              onTap: () {},
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildClientSelector(WidgetRef ref, CartState cart) {
    return InkWell(
      onTap: () => _showCustomerDialog(ref),
      child: Container(
        margin: const EdgeInsets.all(8),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: const Color(0xFFF9FAFB),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: const Color(0xFFE5E7EB)),
        ),
        child: Row(
          children: [
            Icon(
              cart.customer != null ? Icons.person : Icons.person_add_alt_outlined,
              size: 16,
              color: cart.customer != null ? AppTheme.primaryBlue : const Color(0xFF9CA3AF),
            ),
            const SizedBox(width: 8),
            Expanded(
              child: Text(
                cart.customer?.name ?? 'Klient tanlash',
                style: TextStyle(
                  fontSize: 12,
                  color: cart.customer != null ? AppTheme.primaryBlue : const Color(0xFF9CA3AF),
                  fontWeight: cart.customer != null ? FontWeight.w500 : FontWeight.normal,
                ),
              ),
            ),
            if (cart.customer != null)
              InkWell(
                onTap: () => ref.read(cartProvider.notifier).setCustomer(null),
                child: const Icon(Icons.close, size: 14, color: Color(0xFF9CA3AF)),
              ),
          ],
        ),
      ),
    );
  }

  void _showCustomerDialog(WidgetRef ref) {
    // Will be implemented via showDialog
  }

  Widget _buildPaymentMethods() {
    return Padding(
      padding: const EdgeInsets.all(8),
      child: Column(
        children: [
          // Row 1: Click, Payme, Uzcard
          Row(
            children: [
              Expanded(child: _PayBtn(label: 'CLICK', color: const Color(0xFF6366F1), logo: 'C', onTap: onPayClick)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'PayMe', color: const Color(0xFF00AEEF), logo: 'P', onTap: onPayPayme)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'Uzcard', color: const Color(0xFF7C3AED), logo: 'U', onTap: onPayUzcard)),
            ],
          ),
          const SizedBox(height: 6),
          // Row 2: Naqd, Plastik, Humo
          Row(
            children: [
              Expanded(child: _PayBtn(label: 'Naqd (F6)', color: AppTheme.cashColor, logo: '₩', onTap: onPayCash)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'Plastik (F7)', color: AppTheme.cardColor, logo: '💳', onTap: onPayCard)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'Humo (F9)', color: AppTheme.humoColor, logo: 'H', onTap: onPayHumo)),
            ],
          ),
          const SizedBox(height: 6),
          // Row 3: Qarz — to'liq kenglik
          Row(
            children: [
              Expanded(child: _PayBtn(label: 'Qarz', color: AppTheme.debtColor, logo: '🤝', onTap: onPayDebt)),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildTotal(CartState cart) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      child: Column(
        children: [
          if (cart.discountTotal > 0)
            _TotalRow('Chegirma:', '-${_fmt(cart.discountTotal)}', color: AppTheme.danger),
          if (cart.vatTotal > 0)
            _TotalRow('QQS:', _fmt(cart.vatTotal)),
          const Divider(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('Summa', style: TextStyle(fontSize: 13, color: Color(0xFF374151))),
              Text(
                _fmt(cart.total),
                style: const TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF1F2937),
                ),
              ),
            ],
          ),
          // Qty selector row
          Row(
            mainAxisAlignment: MainAxisAlignment.end,
            children: [
              _QtyBtn(label: '1x', onTap: () {}),
              const SizedBox(width: 4),
              _QtyBtn(label: '-', onTap: () {}),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildPrechekButton() {
    return Padding(
      padding: const EdgeInsets.fromLTRB(8, 0, 8, 10),
      child: SizedBox(
        width: double.infinity,
        height: 48,
        child: ElevatedButton(
          onPressed: onPrechek,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppTheme.primaryBlue,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
            elevation: 0,
          ),
          child: const Text(
            'Prechek',
            style: TextStyle(fontSize: 16, fontWeight: FontWeight.w600),
          ),
        ),
      ),
    );
  }

  String _fmt(double v) {
    final s = v.toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(',');
      b.write(s[i]);
    }
    return b.toString();
  }
}

class _ActionBtn extends StatelessWidget {
  final String label;
  final IconData icon;
  final Color color;
  final VoidCallback onTap;

  const _ActionBtn({required this.label, required this.icon, required this.color, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8),
        decoration: BoxDecoration(
          color: color.withOpacity(0.1),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withOpacity(0.2)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(icon, size: 18, color: color),
            const SizedBox(height: 2),
            Text(label, style: TextStyle(fontSize: 10, color: color, fontWeight: FontWeight.w500)),
          ],
        ),
      ),
    );
  }
}

class _PayBtn extends StatelessWidget {
  final String label;
  final Color color;
  final String logo;
  final VoidCallback onTap;
  final bool isBig;

  const _PayBtn({
    required this.label,
    required this.color,
    required this.logo,
    required this.onTap,
    this.isBig = false,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 8, horizontal: 4),
        decoration: BoxDecoration(
          color: color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: color.withOpacity(0.25)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(
              logo,
              style: TextStyle(
                fontSize: isBig ? 16 : 13,
                color: color,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 9,
                color: color,
                fontWeight: FontWeight.w600,
              ),
              textAlign: TextAlign.center,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );
  }
}

class _TotalRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? color;

  const _TotalRow(this.label, this.value, {this.color});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
          Text(value, style: TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: color ?? const Color(0xFF374151))),
        ],
      ),
    );
  }
}

class _QtyBtn extends StatelessWidget {
  final String label;
  final VoidCallback onTap;

  const _QtyBtn({required this.label, required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        decoration: BoxDecoration(
          border: Border.all(color: const Color(0xFFE5E7EB)),
          borderRadius: BorderRadius.circular(6),
        ),
        child: Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF374151))),
      ),
    );
  }
}
