import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';
import '../../pos/providers/pos_provider.dart';
import '../../pos/models/product_model.dart';
import '../../receipt/screens/receipt_screen.dart';

class PaymentScreen extends ConsumerStatefulWidget {
  final String defaultPaymentMethod;

  const PaymentScreen({
    super.key,
    required this.defaultPaymentMethod,
  });

  @override
  ConsumerState<PaymentScreen> createState() => _PaymentScreenState();
}

class _PaymentScreenState extends ConsumerState<PaymentScreen> {
  late String _selectedMethod;
  late TextEditingController _amountCtrl;
  bool _isProcessing = false;
  String _error = '';

  final _methods = [
    {'key': 'cash', 'label': 'Naqd', 'shortcut': 'F6', 'color': 0xFF10B981, 'icon': '💵'},
    {'key': 'card', 'label': 'Plastik', 'shortcut': 'F7', 'color': 0xFF3B82F6, 'icon': '💳'},
    {'key': 'click', 'label': 'Click', 'shortcut': '', 'color': 0xFF6366F1, 'icon': 'C'},
    {'key': 'payme', 'label': 'Payme', 'shortcut': '', 'color': 0xFF00AEEF, 'icon': 'P'},
    {'key': 'humo', 'label': 'Humo', 'shortcut': 'F9', 'color': 0xFFFF6B00, 'icon': 'H'},
    {'key': 'uzcard', 'label': 'Uzcard', 'shortcut': '', 'color': 0xFF7C3AED, 'icon': 'U'},
    {'key': 'debt', 'label': 'Qarz', 'shortcut': '', 'color': 0xFFEF4444, 'icon': '🤝'},
  ];

  @override
  void initState() {
    super.initState();
    _selectedMethod = widget.defaultPaymentMethod;
    final cart = ref.read(cartProvider);
    _amountCtrl = TextEditingController(text: cart.total.toStringAsFixed(0));

    ServicesBinding.instance.keyboard.addHandler(_handleKey);
  }

  @override
  void dispose() {
    _amountCtrl.dispose();
    ServicesBinding.instance.keyboard.removeHandler(_handleKey);
    super.dispose();
  }

  bool _handleKey(KeyEvent event) {
    if (event is KeyDownEvent) {
      if (event.logicalKey == LogicalKeyboardKey.escape) {
        Navigator.of(context).pop();
        return true;
      }
      if (event.logicalKey == LogicalKeyboardKey.enter ||
          event.logicalKey == LogicalKeyboardKey.numpadEnter) {
        _processPayment();
        return true;
      }
    }
    return false;
  }

  double get _paidAmount => double.tryParse(_amountCtrl.text.replaceAll(',', '')) ?? 0;
  double get _cartTotal => ref.read(cartProvider).total;
  double get _change => (_paidAmount - _cartTotal).clamp(0, double.infinity);

  Future<void> _processPayment() async {
    if (_isProcessing) return;
    final cart = ref.read(cartProvider);
    final session = ref.read(posSessionProvider);

    if (cart.isEmpty) {
      setState(() => _error = 'Savat bo\'sh!');
      return;
    }
    if (session == null || session.shiftId == null) {
      setState(() => _error = 'Smena ochilmagan!');
      return;
    }
    if (_paidAmount < _cartTotal && _selectedMethod == 'cash') {
      setState(() => _error = 'To\'lov miqdori yetarli emas!');
      return;
    }

    setState(() { _isProcessing = true; _error = ''; });

    try {
      final api = ref.read(apiServiceProvider);
      final items = cart.items.map((item) => {
        'product_id': item.product.id,
        'quantity': item.quantity,
        'unit_price': item.unitPrice,
        'discount_percent': item.discountPercent,
      }).toList();

      final order = await api.createOrder(
        terminalId: session.terminalId,
        shiftId: session.shiftId!,
        items: items,
        paymentMethod: _selectedMethod,
        paidAmount: _paidAmount,
        customerId: cart.customer?.id,
      );

      if (mounted) {
        setState(() => _isProcessing = false);
        // Show receipt
        Navigator.of(context).pop();
        showDialog(
          context: context,
          barrierDismissible: false,
          builder: (_) => ReceiptScreen(order: order),
        );
        // Clear cart
        ref.read(cartProvider.notifier).clear();
      }
    } catch (e) {
      if (mounted) {
        setState(() { _isProcessing = false; _error = 'Xatolik: $e'; });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final cart = ref.watch(cartProvider);

    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.all(20),
      child: Container(
        width: 800,
        constraints: const BoxConstraints(maxHeight: 620),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 30)],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Header
            _buildHeader(),
            // Body
            Flexible(
              child: Row(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Left: Order summary
                  Expanded(flex: 5, child: _buildOrderSummary(cart)),
                  // Right: Payment
                  Expanded(flex: 4, child: _buildPaymentPanel(cart)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildHeader() {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      decoration: const BoxDecoration(
        color: Color(0xFFF8FAFC),
        borderRadius: BorderRadius.only(topLeft: Radius.circular(16), topRight: Radius.circular(16)),
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Row(
        children: [
          const Icon(Icons.payment_outlined, color: AppTheme.primaryBlue, size: 22),
          const SizedBox(width: 10),
          const Text("To'lov", style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: Color(0xFF1F2937))),
          const Spacer(),
          IconButton(
            icon: const Icon(Icons.close, size: 20),
            onPressed: () => Navigator.of(context).pop(),
            color: const Color(0xFF6B7280),
          ),
        ],
      ),
    );
  }

  Widget _buildOrderSummary(CartState cart) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: const BoxDecoration(
        border: Border(right: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Buyurtma tafsiloti', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF374151))),
          const SizedBox(height: 12),
          // Items list
          Flexible(
            child: ListView.builder(
              shrinkWrap: true,
              itemCount: cart.items.length,
              itemBuilder: (_, i) {
                final item = cart.items[i];
                return Padding(
                  padding: const EdgeInsets.symmetric(vertical: 6),
                  child: Row(
                    children: [
                      SizedBox(
                        width: 24,
                        child: Text('${i + 1}', style: const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
                      ),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(item.product.name, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500), maxLines: 1, overflow: TextOverflow.ellipsis),
                            Text('${_fmt(item.unitPrice)} × ${_fmtQty(item.quantity)}', style: const TextStyle(fontSize: 11, color: Color(0xFF6B7280))),
                          ],
                        ),
                      ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        children: [
                          Text(_fmt(item.totalAmount), style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                          if (item.discountPercent > 0)
                            Text('-${item.discountPercent.toStringAsFixed(0)}%', style: const TextStyle(fontSize: 10, color: AppTheme.danger)),
                        ],
                      ),
                    ],
                  ),
                );
              },
            ),
          ),
          const Divider(),
          // Totals
          if (cart.discountTotal > 0)
            _SummaryRow('Chegirma:', '-${_fmt(cart.discountTotal)}', color: AppTheme.danger),
          if (cart.vatTotal > 0)
            _SummaryRow('QQS:', _fmt(cart.vatTotal)),
          const SizedBox(height: 4),
          _SummaryRow('JAMI:', _fmt(cart.total), isBold: true, large: true),
        ],
      ),
    );
  }

  Widget _buildPaymentPanel(CartState cart) {
    return Padding(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text("To'lov usuli", style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF374151))),
          const SizedBox(height: 10),

          // Payment method buttons
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: _methods.map((m) => _MethodChip(
              label: m['label'] as String,
              shortcut: m['shortcut'] as String,
              color: Color(m['color'] as int),
              isSelected: _selectedMethod == m['key'],
              onTap: () => setState(() {
                _selectedMethod = m['key'] as String;
              }),
            )).toList(),
          ),

          const SizedBox(height: 16),
          const Divider(),
          const SizedBox(height: 12),

          // Amount input
          const Text("To'lov miqdori", style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: Color(0xFF374151))),
          const SizedBox(height: 8),
          TextField(
            controller: _amountCtrl,
            keyboardType: const TextInputType.numberWithOptions(decimal: true),
            autofocus: true,
            onChanged: (_) => setState(() {}),
            style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold),
            textAlign: TextAlign.right,
            decoration: InputDecoration(
              suffixText: "so'm",
              suffixStyle: const TextStyle(fontSize: 14, color: Color(0xFF6B7280)),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
              focusedBorder: OutlineInputBorder(
                borderRadius: BorderRadius.circular(10),
                borderSide: const BorderSide(color: AppTheme.primaryBlue, width: 2),
              ),
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
            ),
          ),

          const SizedBox(height: 8),

          // Quick amount buttons
          Wrap(
            spacing: 6,
            runSpacing: 6,
            children: [
              _QuickAmountBtn(_cartTotal, 'Aniq', onTap: () {
                _amountCtrl.text = _cartTotal.toStringAsFixed(0);
                setState(() {});
              }),
              ...[10000, 50000, 100000, 200000].map((a) => _QuickAmountBtn(
                a.toDouble(),
                _fmt(a.toDouble()),
                onTap: () {
                  _amountCtrl.text = a.toString();
                  setState(() {});
                },
              )),
            ],
          ),

          const SizedBox(height: 12),

          // Change amount
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: _change > 0 ? const Color(0xFFF0FDF4) : const Color(0xFFF9FAFB),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: _change > 0 ? const Color(0xFF10B981).withOpacity(0.3) : const Color(0xFFE5E7EB)),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text('Qaytim:', style: TextStyle(fontSize: 14, color: _change > 0 ? const Color(0xFF059669) : const Color(0xFF6B7280))),
                Text(
                  '${_fmt(_change)} so\'m',
                  style: TextStyle(
                    fontSize: 20,
                    fontWeight: FontWeight.bold,
                    color: _change > 0 ? const Color(0xFF059669) : const Color(0xFF374151),
                  ),
                ),
              ],
            ),
          ),

          // Error
          if (_error.isNotEmpty) ...[
            const SizedBox(height: 8),
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: AppTheme.danger.withOpacity(0.1), borderRadius: BorderRadius.circular(8)),
              child: Row(
                children: [
                  const Icon(Icons.error_outline, color: AppTheme.danger, size: 16),
                  const SizedBox(width: 8),
                  Expanded(child: Text(_error, style: const TextStyle(color: AppTheme.danger, fontSize: 12))),
                ],
              ),
            ),
          ],

          const Spacer(),

          // Submit button
          SizedBox(
            width: double.infinity,
            height: 50,
            child: ElevatedButton(
              onPressed: _isProcessing ? null : _processPayment,
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryBlue,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                elevation: 0,
              ),
              child: _isProcessing
                  ? const SizedBox(width: 24, height: 24, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2.5))
                  : Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        const Icon(Icons.check_circle_outline, size: 20),
                        const SizedBox(width: 8),
                        Text(
                          "Bo'lish (F8) • ${_fmt(_cartTotal)} so'm",
                          style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600),
                        ),
                      ],
                    ),
            ),
          ),
          const SizedBox(height: 8),
          SizedBox(
            width: double.infinity,
            height: 38,
            child: OutlinedButton(
              onPressed: () => Navigator.of(context).pop(),
              style: OutlinedButton.styleFrom(
                side: const BorderSide(color: Color(0xFFE5E7EB)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('Bekor qilish (ESC)', style: TextStyle(color: Color(0xFF6B7280), fontSize: 13)),
            ),
          ),
        ],
      ),
    );
  }

  String _fmt(double v) {
    if (v == 0) return '0';
    final s = v.toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(',');
      b.write(s[i]);
    }
    return b.toString();
  }

  String _fmtQty(double v) => v == v.roundToDouble() ? v.toInt().toString() : v.toStringAsFixed(3);
}

class _MethodChip extends StatelessWidget {
  final String label;
  final String shortcut;
  final Color color;
  final bool isSelected;
  final VoidCallback onTap;

  const _MethodChip({
    required this.label,
    required this.shortcut,
    required this.color,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(8),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 150),
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
        decoration: BoxDecoration(
          color: isSelected ? color : color.withOpacity(0.08),
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: isSelected ? color : color.withOpacity(0.3), width: isSelected ? 2 : 1),
          boxShadow: isSelected ? [BoxShadow(color: color.withOpacity(0.3), blurRadius: 8, offset: const Offset(0, 2))] : null,
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Text(label, style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: isSelected ? Colors.white : color)),
            if (shortcut.isNotEmpty)
              Text(shortcut, style: TextStyle(fontSize: 10, color: isSelected ? Colors.white70 : color.withOpacity(0.7))),
          ],
        ),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? color;
  final bool isBold;
  final bool large;

  const _SummaryRow(this.label, this.value, {this.color, this.isBold = false, this.large = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: large ? 15 : 13, fontWeight: isBold ? FontWeight.bold : FontWeight.normal, color: const Color(0xFF374151))),
          Text(value, style: TextStyle(fontSize: large ? 18 : 13, fontWeight: isBold ? FontWeight.bold : FontWeight.w500, color: color ?? const Color(0xFF1F2937))),
        ],
      ),
    );
  }
}

class _QuickAmountBtn extends StatelessWidget {
  final double amount;
  final String label;
  final VoidCallback onTap;

  const _QuickAmountBtn(this.amount, this.label, {required this.onTap});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(6),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
        decoration: BoxDecoration(
          color: const Color(0xFFF3F4F6),
          borderRadius: BorderRadius.circular(6),
          border: Border.all(color: const Color(0xFFE5E7EB)),
        ),
        child: Text(label, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500, color: Color(0xFF374151))),
      ),
    );
  }
}
