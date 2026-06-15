import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';

class ShiftReportScreen extends ConsumerStatefulWidget {
  final int shiftId;
  const ShiftReportScreen({super.key, required this.shiftId});

  @override
  ConsumerState<ShiftReportScreen> createState() => _ShiftReportScreenState();
}

class _ShiftReportScreenState extends ConsumerState<ShiftReportScreen> {
  bool _loading = true;
  Map<String, dynamic>? _report;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    try {
      final api = ref.read(apiServiceProvider);
      final data = await api.getShiftReport(widget.shiftId);
      setState(() { _report = data; _loading = false; });
    } catch (e) {
      setState(() => _loading = false);
    }
  }

  String _fmt(dynamic v) {
    final d = double.tryParse(v?.toString() ?? '0') ?? 0;
    final s = d.toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(' ');
      b.write(s[i]);
    }
    return b.toString();
  }

  @override
  Widget build(BuildContext context) {
    final shift = _report?['shift'];
    final stats = _report?['stats'];

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topLeft,
            end: Alignment.bottomRight,
            colors: [Color(0xFF0D2B5C), Color(0xFF1A4080)],
          ),
        ),
        child: Center(
          child: Container(
            width: 560,
            constraints: const BoxConstraints(maxHeight: 720),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 30)],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                // Header
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    color: AppTheme.primaryBlue.withOpacity(0.08),
                    borderRadius: const BorderRadius.only(topLeft: Radius.circular(20), topRight: Radius.circular(20)),
                    border: const Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 44, height: 44,
                        decoration: BoxDecoration(color: AppTheme.primaryBlue.withOpacity(0.1), borderRadius: BorderRadius.circular(12)),
                        child: const Icon(Icons.assessment, color: AppTheme.primaryBlue, size: 24),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Smena hisoboti', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1F2937))),
                          if (shift != null)
                            Text('${shift['shift_number'] ?? ''}', style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                        ],
                      ),
                    ],
                  ),
                ),

                Flexible(
                  child: _loading
                      ? const Center(child: CircularProgressIndicator())
                      : _report == null
                          ? const Center(child: Text('Ma\'lumot yuklanmadi'))
                          : SingleChildScrollView(
                              padding: const EdgeInsets.all(20),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  // Summary cards
                                  _buildSummary(stats ?? {}),
                                  const SizedBox(height: 16),

                                  // By payment
                                  const Text("To'lov usuli bo'yicha", style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                                  const SizedBox(height: 8),
                                  _buildPaymentBreakdown(stats?['by_payment'] ?? {}),

                                  const SizedBox(height: 16),

                                  // Cash reconciliation
                                  if (shift != null) _buildCashReconciliation(shift),
                                ],
                              ),
                            ),
                ),

                // Actions
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: const BoxDecoration(border: Border(top: BorderSide(color: Color(0xFFE5E7EB)))),
                  child: Row(
                    children: [
                      Expanded(
                        child: ElevatedButton.icon(
                          onPressed: () => context.go('/login'),
                          icon: const Icon(Icons.logout, size: 18),
                          label: const Text('Chiqish'),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: AppTheme.primaryBlue,
                            foregroundColor: Colors.white,
                            padding: const EdgeInsets.symmetric(vertical: 12),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                            elevation: 0,
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildSummary(Map<String, dynamic> stats) {
    return GridView.count(
      crossAxisCount: 3,
      shrinkWrap: true,
      physics: const NeverScrollableScrollPhysics(),
      crossAxisSpacing: 8,
      mainAxisSpacing: 8,
      childAspectRatio: 1.6,
      children: [
        _Card('Savdolar', '${stats['total_orders'] ?? 0}', Icons.receipt_long, AppTheme.primaryBlue),
        _Card('Jami tushum', '${_fmt(stats['total_sales'])} so\'m', Icons.monetization_on, AppTheme.success),
        _Card('Chegirma', '${_fmt(stats['total_discount'])} so\'m', Icons.discount_outlined, AppTheme.danger),
        _Card('Qaytarishlar', '${stats['total_returns'] ?? 0}', Icons.undo, AppTheme.warning),
        _Card('Kirim', '${_fmt(stats['cash_in'])} so\'m', Icons.arrow_downward, AppTheme.success),
        _Card('Chiqim', '${_fmt(stats['cash_out'])} so\'m', Icons.arrow_upward, AppTheme.danger),
      ],
    );
  }

  Widget _buildPaymentBreakdown(Map<String, dynamic> payments) {
    final labels = {'cash': 'Naqd', 'card': 'Plastik', 'click': 'Click', 'payme': 'Payme', 'humo': 'Humo', 'uzcard': 'Uzcard', 'debt': 'Qarz'};
    final colors = {
      'cash': AppTheme.cashColor, 'card': AppTheme.cardColor, 'click': AppTheme.clickColor,
      'payme': AppTheme.paymeColor, 'humo': AppTheme.humoColor, 'uzcard': AppTheme.uzcardColor, 'debt': AppTheme.debtColor,
    };

    return Column(
      children: labels.entries.map((e) {
        final val = double.tryParse(payments[e.key]?.toString() ?? '0') ?? 0;
        if (val == 0) return const SizedBox.shrink();
        return Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Row(
            children: [
              Container(width: 12, height: 12, decoration: BoxDecoration(color: colors[e.key], borderRadius: BorderRadius.circular(3))),
              const SizedBox(width: 8),
              Text(e.value, style: const TextStyle(fontSize: 13, color: Color(0xFF374151))),
              const Spacer(),
              Text('${_fmt(val)} so\'m', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
            ],
          ),
        );
      }).toList(),
    );
  }

  Widget _buildCashReconciliation(Map<String, dynamic> shift) {
    final openCash = double.tryParse(shift['opening_cash']?.toString() ?? '0') ?? 0;
    final closeCash = double.tryParse(shift['closing_cash']?.toString() ?? '0') ?? 0;
    final expected = double.tryParse(shift['expected_cash']?.toString() ?? '0') ?? 0;
    final diff = double.tryParse(shift['difference']?.toString() ?? '0') ?? 0;

    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFFF8FAFC),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFE5E7EB)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text('Kassa chiqib ketish', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
          const SizedBox(height: 8),
          _CashRow("Boshlang'ich saldo:", '${_fmt(openCash)} so\'m'),
          _CashRow('Kutilgan:', '${_fmt(expected)} so\'m'),
          _CashRow('Haqiqiy:', '${_fmt(closeCash)} so\'m'),
          const Divider(height: 12),
          _CashRow(
            'Farq:',
            '${diff >= 0 ? '+' : ''}${_fmt(diff)} so\'m',
            color: diff == 0 ? AppTheme.success : diff > 0 ? AppTheme.warning : AppTheme.danger,
            bold: true,
          ),
        ],
      ),
    );
  }
}

class _Card extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _Card(this.label, this.value, this.icon, this.color);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.07),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, mainAxisAlignment: MainAxisAlignment.center, children: [
        Icon(icon, color: color, size: 18),
        const SizedBox(height: 4),
        Text(value, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color), maxLines: 1, overflow: TextOverflow.ellipsis),
        Text(label, style: const TextStyle(fontSize: 9, color: Color(0xFF6B7280))),
      ]),
    );
  }
}

class _CashRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? color;
  final bool bold;
  const _CashRow(this.label, this.value, {this.color, this.bold = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
          Text(value, style: TextStyle(fontSize: 13, fontWeight: bold ? FontWeight.bold : FontWeight.w500, color: color ?? const Color(0xFF1F2937))),
        ],
      ),
    );
  }
}
