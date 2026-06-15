import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:printing/printing.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import '../../../core/theme/app_theme.dart';

class ReceiptScreen extends ConsumerStatefulWidget {
  final Map<String, dynamic> order;
  const ReceiptScreen({super.key, required this.order});

  @override
  ConsumerState<ReceiptScreen> createState() => _ReceiptScreenState();
}

class _ReceiptScreenState extends ConsumerState<ReceiptScreen> {
  bool _printing = false;

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

  final _paymentLabels = {
    'cash': 'Naqd', 'card': 'Plastik', 'click': 'Click',
    'payme': 'Payme', 'humo': 'Humo', 'uzcard': 'Uzcard', 'debt': 'Qarz',
  };

  Future<void> _print() async {
    setState(() => _printing = true);
    try {
      final doc = await _buildPdf();
      await Printing.layoutPdf(onLayout: (_) => doc);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Chop etishda xatolik: $e')));
      }
    } finally {
      if (mounted) setState(() => _printing = false);
    }
  }

  Future<List<int>> _buildPdf() async {
    final doc = pw.Document();
    final order = widget.order;
    final items = (order['items'] as List?) ?? [];
    final org = order['branch']?['organization'] ?? {};
    final branch = order['branch'] ?? {};
    final terminal = order['terminal'] ?? {};
    final cashier = order['user'] ?? {};

    doc.addPage(pw.Page(
      pageFormat: const PdfPageFormat(80 * PdfPageFormat.mm, double.infinity, marginAll: 5 * PdfPageFormat.mm),
      build: (ctx) => pw.Column(
        crossAxisAlignment: pw.CrossAxisAlignment.stretch,
        children: [
          // Header
          pw.Center(child: pw.Text(org['name'] ?? 'YesPOS',
              style: pw.TextStyle(fontSize: 14, fontWeight: pw.FontWeight.bold))),
          pw.Center(child: pw.Text(branch['name'] ?? '', style: const pw.TextStyle(fontSize: 9))),
          pw.Center(child: pw.Text(org['phone'] ?? '', style: const pw.TextStyle(fontSize: 9))),
          pw.Divider(),

          // Receipt info
          pw.Row(mainAxisAlignment: pw.MainAxisAlignment.spaceBetween, children: [
            pw.Text('Chek №: ${order['receipt_number'] ?? ''}', style: const pw.TextStyle(fontSize: 9)),
            pw.Text(order['created_at'] != null ? _formatDate(order['created_at']) : '', style: const pw.TextStyle(fontSize: 9)),
          ]),
          pw.Text('Kassir: ${cashier['name'] ?? ''}', style: const pw.TextStyle(fontSize: 9)),
          pw.Text('Terminal: ${terminal['name'] ?? ''}', style: const pw.TextStyle(fontSize: 9)),
          pw.Divider(),

          // Items
          pw.Row(children: [
            pw.Expanded(child: pw.Text('Nomi', style: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold))),
            pw.SizedBox(width: 40, child: pw.Text('Narx', style: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold), textAlign: pw.TextAlign.right)),
            pw.SizedBox(width: 30, child: pw.Text('Miq', style: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold), textAlign: pw.TextAlign.right)),
            pw.SizedBox(width: 50, child: pw.Text('Summa', style: pw.TextStyle(fontSize: 9, fontWeight: pw.FontWeight.bold), textAlign: pw.TextAlign.right)),
          ]),
          pw.Divider(),

          for (final item in items) ...[
            pw.Row(children: [
              pw.Expanded(child: pw.Text(item['product_name'] ?? '', style: const pw.TextStyle(fontSize: 9))),
              pw.SizedBox(width: 40, child: pw.Text(_fmt(item['unit_price']), style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right)),
              pw.SizedBox(width: 30, child: pw.Text('${item['quantity']}', style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right)),
              pw.SizedBox(width: 50, child: pw.Text(_fmt(item['total_amount']), style: const pw.TextStyle(fontSize: 9), textAlign: pw.TextAlign.right)),
            ]),
          ],

          pw.Divider(),

          // Totals
          if ((double.tryParse(order['discount_amount']?.toString() ?? '0') ?? 0) > 0)
            pw.Row(mainAxisAlignment: pw.MainAxisAlignment.spaceBetween, children: [
              pw.Text('Chegirma:', style: const pw.TextStyle(fontSize: 9)),
              pw.Text('-${_fmt(order['discount_amount'])} so\'m', style: const pw.TextStyle(fontSize: 9)),
            ]),

          pw.Row(mainAxisAlignment: pw.MainAxisAlignment.spaceBetween, children: [
            pw.Text('JAMI:', style: pw.TextStyle(fontSize: 12, fontWeight: pw.FontWeight.bold)),
            pw.Text('${_fmt(order['total_amount'])} so\'m', style: pw.TextStyle(fontSize: 12, fontWeight: pw.FontWeight.bold)),
          ]),
          pw.Row(mainAxisAlignment: pw.MainAxisAlignment.spaceBetween, children: [
            pw.Text("To'langan:", style: const pw.TextStyle(fontSize: 9)),
            pw.Text('${_fmt(order['paid_amount'])} so\'m', style: const pw.TextStyle(fontSize: 9)),
          ]),
          if ((double.tryParse(order['change_amount']?.toString() ?? '0') ?? 0) > 0)
            pw.Row(mainAxisAlignment: pw.MainAxisAlignment.spaceBetween, children: [
              pw.Text('Qaytim:', style: const pw.TextStyle(fontSize: 9)),
              pw.Text('${_fmt(order['change_amount'])} so\'m', style: const pw.TextStyle(fontSize: 9)),
            ]),

          pw.Divider(),
          pw.Center(child: pw.Text("To'lov: ${_paymentLabels[order['payment_method']] ?? order['payment_method']}", style: const pw.TextStyle(fontSize: 9))),
          pw.SizedBox(height: 10),
          pw.Center(child: pw.Text('Xaridingiz uchun rahmat!', style: pw.TextStyle(fontSize: 10, fontWeight: pw.FontWeight.bold))),
          pw.Center(child: pw.Text('YesPOS - yespos.uz', style: const pw.TextStyle(fontSize: 8))),
        ],
      ),
    ));

    return doc.save();
  }

  String _formatDate(String dateStr) {
    try {
      final dt = DateTime.parse(dateStr);
      return '${dt.day.toString().padLeft(2, '0')}.${dt.month.toString().padLeft(2, '0')}.${dt.year} '
          '${dt.hour.toString().padLeft(2, '0')}:${dt.minute.toString().padLeft(2, '0')}';
    } catch (_) {
      return dateStr;
    }
  }

  @override
  Widget build(BuildContext context) {
    final order = widget.order;
    final items = (order['items'] as List?) ?? [];
    final org = order['branch']?['organization'] ?? {};
    final branch = order['branch'] ?? {};
    final terminal = order['terminal'] ?? {};
    final cashier = order['user'] ?? {};
    final payMethod = _paymentLabels[order['payment_method']] ?? order['payment_method'] ?? '';
    final change = double.tryParse(order['change_amount']?.toString() ?? '0') ?? 0;

    return Dialog(
      backgroundColor: Colors.transparent,
      child: Container(
        width: 420,
        constraints: const BoxConstraints(maxHeight: 700),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 30)],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Success header
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.success.withOpacity(0.1),
                borderRadius: const BorderRadius.only(topLeft: Radius.circular(16), topRight: Radius.circular(16)),
              ),
              child: Row(
                children: [
                  Container(
                    width: 44,
                    height: 44,
                    decoration: BoxDecoration(color: AppTheme.success, borderRadius: BorderRadius.circular(12)),
                    child: const Icon(Icons.check, color: Colors.white, size: 26),
                  ),
                  const SizedBox(width: 12),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Savdo muvaffaqiyatli!', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF065F46))),
                      Text('Chek № ${order['receipt_number'] ?? '—'}', style: const TextStyle(fontSize: 12, color: Color(0xFF059669))),
                    ],
                  ),
                ],
              ),
            ),

            // Receipt content
            Flexible(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16),
                child: Column(
                  children: [
                    // Org info
                    _ReceiptHeader(orgName: org['name'] ?? 'YesPOS', branchName: branch['name'] ?? ''),

                    // Meta
                    const Divider(),
                    _ReceiptMeta(
                      cashier: cashier['name'] ?? '—',
                      terminal: terminal['name'] ?? '—',
                      payMethod: payMethod,
                      date: order['created_at'] != null ? _formatDate(order['created_at']) : '',
                    ),

                    const Divider(),

                    // Items
                    ...items.map<Widget>((item) => _ReceiptItem(
                      name: item['product_name'] ?? '',
                      sku: item['product_sku'] ?? '',
                      qty: item['quantity']?.toString() ?? '0',
                      price: _fmt(item['unit_price']),
                      total: _fmt(item['total_amount']),
                      discount: item['discount_percent'] != null && double.parse(item['discount_percent'].toString()) > 0
                          ? '${item['discount_percent']}%'
                          : null,
                    )),

                    const Divider(thickness: 1.5),

                    // Totals
                    if ((double.tryParse(order['discount_amount']?.toString() ?? '0') ?? 0) > 0)
                      _TRow('Chegirma:', '-${_fmt(order['discount_amount'])} so\'m', color: AppTheme.danger),
                    _TRow('JAMI:', '${_fmt(order['total_amount'])} so\'m', bold: true, large: true),
                    _TRow("To'langan:", '${_fmt(order['paid_amount'])} so\'m', color: AppTheme.primaryBlue),
                    if (change > 0)
                      _TRow('Qaytim:', '${_fmt(change)} so\'m', color: AppTheme.success),

                    const SizedBox(height: 12),
                    const Text('Xaridingiz uchun rahmat!',
                        style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF374151))),
                  ],
                ),
              ),
            ),

            // Actions
            Container(
              padding: const EdgeInsets.all(12),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: Color(0xFFE5E7EB))),
              ),
              child: Row(
                children: [
                  Expanded(
                    child: OutlinedButton.icon(
                      onPressed: _printing ? null : _print,
                      icon: _printing
                          ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2))
                          : const Icon(Icons.print, size: 16),
                      label: Text(_printing ? 'Chop etilmoqda...' : 'Chop etish'),
                      style: OutlinedButton.styleFrom(
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        side: const BorderSide(color: AppTheme.primaryBlue),
                        foregroundColor: AppTheme.primaryBlue,
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
                      ),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: ElevatedButton.icon(
                      onPressed: () => Navigator.of(context).pop(),
                      icon: const Icon(Icons.add_circle_outline, size: 16),
                      label: const Text('Yangi savdo'),
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
    );
  }
}

class _ReceiptHeader extends StatelessWidget {
  final String orgName;
  final String branchName;
  const _ReceiptHeader({required this.orgName, required this.branchName});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Text(orgName, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold), textAlign: TextAlign.center),
        if (branchName.isNotEmpty)
          Text(branchName, style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)), textAlign: TextAlign.center),
      ],
    );
  }
}

class _ReceiptMeta extends StatelessWidget {
  final String cashier;
  final String terminal;
  final String payMethod;
  final String date;
  const _ReceiptMeta({required this.cashier, required this.terminal, required this.payMethod, required this.date});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        _MetaRow('Kassir:', cashier),
        _MetaRow('Terminal:', terminal),
        _MetaRow("To'lov:", payMethod),
        _MetaRow('Sana:', date),
      ],
    );
  }
}

class _MetaRow extends StatelessWidget {
  final String label;
  final String value;
  const _MetaRow(this.label, this.value);

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF6B7280))),
          Text(value, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w500)),
        ],
      ),
    );
  }
}

class _ReceiptItem extends StatelessWidget {
  final String name;
  final String sku;
  final String qty;
  final String price;
  final String total;
  final String? discount;
  const _ReceiptItem({required this.name, required this.sku, required this.qty, required this.price, required this.total, this.discount});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(name, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w500)),
                Text('$sku • $price × $qty', style: const TextStyle(fontSize: 10, color: Color(0xFF9CA3AF))),
                if (discount != null)
                  Text('Chegirma: $discount', style: const TextStyle(fontSize: 10, color: AppTheme.danger)),
              ],
            ),
          ),
          Text(total, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
        ],
      ),
    );
  }
}

class _TRow extends StatelessWidget {
  final String label;
  final String value;
  final Color? color;
  final bool bold;
  final bool large;
  const _TRow(this.label, this.value, {this.color, this.bold = false, this.large = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label, style: TextStyle(fontSize: large ? 14 : 12, fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
          Text(value, style: TextStyle(fontSize: large ? 16 : 12, fontWeight: bold ? FontWeight.bold : FontWeight.w500, color: color)),
        ],
      ),
    );
  }
}
