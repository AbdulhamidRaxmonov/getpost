import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../providers/pos_provider.dart';
import '../models/product_model.dart';
import 'customer_select_dialog.dart';

class CartPanel extends ConsumerStatefulWidget {
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
  ConsumerState<CartPanel> createState() => _CartPanelState();
}

class _CartPanelState extends ConsumerState<CartPanel> {

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(left: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Column(
        children: [
          _buildTopActions(ref, cart),
          _buildClientSelector(ref, cart),
          const Spacer(),
          _buildPaymentMethods(),
          const Divider(height: 1),
          _buildTotal(cart),
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
          Expanded(
            child: _ActionBtn(
              label: "O'chirish",
              icon: Icons.close,
              color: AppTheme.danger,
              onTap: () => ref.read(cartProvider.notifier).clear(),
            ),
          ),
          const SizedBox(width: 8),
          Expanded(
            child: _ActionBtn(
              label: 'Cheklar',
              icon: Icons.receipt_long_outlined,
              color: const Color(0xFF374151),
              onTap: () {},
            ),
          ),
          const SizedBox(width: 8),
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
    return Column(
      children: [
        // Mijoz tanlash tugmasi
        InkWell(
          onTap: () => _openCustomerDialog(ref),
          child: Container(
            margin: const EdgeInsets.fromLTRB(8, 8, 8, 0),
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
            decoration: BoxDecoration(
              color: cart.customer != null
                  ? AppTheme.primaryBlue.withOpacity(0.05)
                  : const Color(0xFFF9FAFB),
              borderRadius: BorderRadius.circular(8),
              border: Border.all(
                color: cart.customer != null
                    ? AppTheme.primaryBlue.withOpacity(0.3)
                    : const Color(0xFFE5E7EB),
              ),
            ),
            child: Row(
              children: [
                Icon(
                  cart.customer != null
                      ? Icons.person
                      : Icons.person_add_alt_outlined,
                  size: 16,
                  color: cart.customer != null
                      ? AppTheme.primaryBlue
                      : const Color(0xFF9CA3AF),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: Text(
                    cart.customer?.name ?? 'Klient tanlash',
                    style: TextStyle(
                      fontSize: 12,
                      color: cart.customer != null
                          ? AppTheme.primaryBlue
                          : const Color(0xFF9CA3AF),
                      fontWeight: cart.customer != null
                          ? FontWeight.w500
                          : FontWeight.normal,
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                if (cart.customer != null) ...[
                  // Qarz ko'rsatish tugmasi
                  InkWell(
                    onTap: () => _showDebtInfo(ref, cart.customer!),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 6, vertical: 2),
                      decoration: BoxDecoration(
                        color: cart.customer!.balance < 0
                            ? AppTheme.danger.withOpacity(0.1)
                            : cart.customer!.balance > 0
                                ? AppTheme.success.withOpacity(0.1)
                                : const Color(0xFFF3F4F6),
                        borderRadius: BorderRadius.circular(4),
                      ),
                      child: Text(
                        cart.customer!.balance < 0
                            ? '💸 Qarz'
                            : cart.customer!.balance > 0
                                ? '✓ Balans'
                                : '✓ Toza',
                        style: TextStyle(
                          fontSize: 10,
                          fontWeight: FontWeight.w600,
                          color: cart.customer!.balance < 0
                              ? AppTheme.danger
                              : cart.customer!.balance > 0
                                  ? AppTheme.success
                                  : const Color(0xFF6B7280),
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 4),
                  // O'chirish
                  InkWell(
                    onTap: () =>
                        ref.read(cartProvider.notifier).setCustomer(null),
                    child: const Icon(Icons.close,
                        size: 14, color: Color(0xFF9CA3AF)),
                  ),
                ],
              ],
            ),
          ),
        ),

        // Mijoz tanlangan bo'lsa — qarz/balans va chegirma qatorini ko'rsatish
        if (cart.customer != null) _buildCustomerInfo(cart.customer!),
      ],
    );
  }

  Widget _buildCustomerInfo(CustomerModel customer) {
    final hasDebt = customer.balance < 0;
    final hasCredit = customer.balance > 0;
    final hasDiscount = customer.discountPercent > 0;

    if (!hasDebt && !hasCredit && !hasDiscount) {
      return const SizedBox.shrink();
    }

    return Container(
      margin: const EdgeInsets.fromLTRB(8, 4, 8, 0),
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: hasDebt
            ? AppTheme.danger.withOpacity(0.05)
            : const Color(0xFFF0FDF4),
        borderRadius: BorderRadius.circular(6),
        border: Border.all(
          color: hasDebt
              ? AppTheme.danger.withOpacity(0.2)
              : AppTheme.success.withOpacity(0.2),
        ),
      ),
      child: Row(
        children: [
          Icon(
            hasDebt ? Icons.warning_amber : Icons.check_circle_outline,
            size: 14,
            color: hasDebt ? AppTheme.danger : AppTheme.success,
          ),
          const SizedBox(width: 6),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (hasDebt)
                  Text(
                    'Qarz: ${_fmt(customer.balance.abs())} so\'m',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.danger,
                    ),
                  )
                else if (hasCredit)
                  Text(
                    'Balans: +${_fmt(customer.balance)} so\'m',
                    style: const TextStyle(
                      fontSize: 11,
                      fontWeight: FontWeight.w600,
                      color: AppTheme.success,
                    ),
                  ),
                if (hasDiscount)
                  Text(
                    'Chegirma: ${customer.discountPercent.toStringAsFixed(0)}%',
                    style: const TextStyle(
                      fontSize: 11,
                      color: AppTheme.success,
                    ),
                  ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  void _openCustomerDialog(WidgetRef ref) {
    showDialog(
      context: context,
      builder: (_) => const CustomerSelectDialog(),
    );
  }

  void _showDebtInfo(WidgetRef ref, CustomerModel customer) {
    showDialog(
      context: context,
      builder: (_) => CustomerDebtDialog(customer: customer),
    );
  }

  Widget _buildPaymentMethods() {
    return Padding(
      padding: const EdgeInsets.all(8),
      child: Column(
        children: [
          Row(
            children: [
              Expanded(child: _PayBtn(label: 'CLICK', color: const Color(0xFF6366F1), logo: 'C', onTap: widget.onPayClick)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'PayMe', color: const Color(0xFF00AEEF), logo: 'P', onTap: widget.onPayPayme)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'Uzcard', color: const Color(0xFF7C3AED), logo: 'U', onTap: widget.onPayUzcard)),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              Expanded(child: _PayBtn(label: 'Naqd (F6)', color: AppTheme.cashColor, logo: '₩', onTap: widget.onPayCash)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'Plastik (F7)', color: AppTheme.cardColor, logo: '💳', onTap: widget.onPayCard)),
              const SizedBox(width: 6),
              Expanded(child: _PayBtn(label: 'Humo (F9)', color: AppTheme.humoColor, logo: 'H', onTap: widget.onPayHumo)),
            ],
          ),
          const SizedBox(height: 6),
          Row(
            children: [
              Expanded(child: _PayBtn(label: 'Qarz', color: AppTheme.debtColor, logo: '🤝', onTap: widget.onPayDebt)),
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
          onPressed: widget.onPrechek,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppTheme.primaryBlue,
            foregroundColor: Colors.white,
            shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(10)),
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
