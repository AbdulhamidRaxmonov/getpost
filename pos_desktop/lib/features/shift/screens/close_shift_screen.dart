import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';

class CloseShiftScreen extends ConsumerStatefulWidget {
  const CloseShiftScreen({super.key});

  @override
  ConsumerState<CloseShiftScreen> createState() => _CloseShiftScreenState();
}

class _CloseShiftScreenState extends ConsumerState<CloseShiftScreen> {
  final _cashCtrl = TextEditingController(text: '0');
  final _noteCtrl = TextEditingController();
  bool _isLoading = false;
  bool _loadingStats = true;
  String _error = '';
  Map<String, dynamic>? _shiftStats;

  @override
  void initState() {
    super.initState();
    _loadStats();
  }

  @override
  void dispose() {
    _cashCtrl.dispose();
    _noteCtrl.dispose();
    super.dispose();
  }

  Future<void> _loadStats() async {
    final session = ref.read(posSessionProvider);
    if (session?.shiftId == null) return;

    try {
      final api = ref.read(apiServiceProvider);
      final data = await api.getShiftReport(session!.shiftId!);
      setState(() {
        _shiftStats = data;
        _loadingStats = false;
      });
    } catch (e) {
      setState(() => _loadingStats = false);
    }
  }

  Future<void> _closeShift() async {
    final session = ref.read(posSessionProvider);
    if (session?.shiftId == null) return;

    final cash = double.tryParse(_cashCtrl.text.replaceAll(',', '')) ?? 0;
    setState(() { _isLoading = true; _error = ''; });

    try {
      final api = ref.read(apiServiceProvider);
      await api.closeShift(
        shiftId: session!.shiftId!,
        closingCash: cash,
        closingNote: _noteCtrl.text.trim().isNotEmpty ? _noteCtrl.text.trim() : null,
      );

      ref.read(posSessionProvider.notifier).closeShift();

      if (mounted) {
        context.go('/shift/report/${session.shiftId}');
      }
    } catch (e) {
      setState(() { _isLoading = false; _error = e.toString(); });
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
    final session = ref.watch(posSessionProvider);
    final stats = _shiftStats?['stats'];

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
            width: 520,
            constraints: const BoxConstraints(maxHeight: 700),
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
                  decoration: const BoxDecoration(
                    color: Color(0xFFFFF7ED),
                    borderRadius: BorderRadius.only(topLeft: Radius.circular(20), topRight: Radius.circular(20)),
                    border: Border(bottom: BorderSide(color: Color(0xFFFED7AA))),
                  ),
                  child: Row(
                    children: [
                      Container(
                        width: 44,
                        height: 44,
                        decoration: BoxDecoration(color: AppTheme.warning.withOpacity(0.15), borderRadius: BorderRadius.circular(12)),
                        child: const Icon(Icons.lock_clock, color: AppTheme.warning, size: 24),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Smenani yopish', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF92400E))),
                          Text('${session?.terminalName ?? ''} • ${session?.branchName ?? ''}',
                              style: const TextStyle(fontSize: 12, color: Color(0xFFB45309))),
                        ],
                      ),
                      const Spacer(),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => context.go('/pos'),
                        color: const Color(0xFF6B7280),
                      ),
                    ],
                  ),
                ),

                // Body
                Flexible(
                  child: SingleChildScrollView(
                    padding: const EdgeInsets.all(20),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        // Shift stats
                        if (_loadingStats)
                          const Center(child: Padding(
                            padding: EdgeInsets.all(20),
                            child: CircularProgressIndicator(),
                          ))
                        else if (stats != null)
                          _buildStatsGrid(stats),

                        const SizedBox(height: 20),
                        const Divider(),
                        const SizedBox(height: 12),

                        // Closing cash
                        const Text("Kassadagi naqd pul (haqiqiy)",
                            style: TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: Color(0xFF374151))),
                        const SizedBox(height: 8),
                        TextField(
                          controller: _cashCtrl,
                          keyboardType: const TextInputType.numberWithOptions(decimal: true),
                          autofocus: true,
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

                        const SizedBox(height: 12),

                        // Note
                        TextField(
                          controller: _noteCtrl,
                          maxLines: 2,
                          decoration: InputDecoration(
                            hintText: 'Izoh (ixtiyoriy)',
                            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
                            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
                            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppTheme.primaryBlue, width: 2)),
                            contentPadding: const EdgeInsets.all(12),
                          ),
                        ),

                        if (_error.isNotEmpty) ...[
                          const SizedBox(height: 12),
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

                        const SizedBox(height: 20),

                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton(
                                onPressed: () => context.go('/pos'),
                                style: OutlinedButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  side: const BorderSide(color: Color(0xFFE5E7EB)),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                ),
                                child: const Text('Bekor qilish', style: TextStyle(color: Color(0xFF6B7280))),
                              ),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              flex: 2,
                              child: ElevatedButton.icon(
                                onPressed: _isLoading ? null : _closeShift,
                                icon: _isLoading
                                    ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                    : const Icon(Icons.lock),
                                label: Text(_isLoading ? 'Yopilmoqda...' : 'Smenani yopish',
                                    style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: AppTheme.warning,
                                  foregroundColor: Colors.white,
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                                  elevation: 0,
                                ),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildStatsGrid(Map<String, dynamic> stats) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Smena statistikasi', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF374151))),
        const SizedBox(height: 10),
        GridView.count(
          crossAxisCount: 3,
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisSpacing: 8,
          mainAxisSpacing: 8,
          childAspectRatio: 1.8,
          children: [
            _StatCard('Savdolar', '${stats['total_orders'] ?? 0}', Icons.receipt_long, const Color(0xFF3B82F6)),
            _StatCard('Jami tushum', '${_fmt(stats['total_sales'])} so\'m', Icons.monetization_on, const Color(0xFF10B981)),
            _StatCard('Qaytarishlar', '${stats['total_returns'] ?? 0}', Icons.undo, const Color(0xFFEF4444)),
            _StatCard('Naqd', '${_fmt(stats['by_payment']?['cash'])} so\'m', Icons.money, const Color(0xFF10B981)),
            _StatCard('Plastik', '${_fmt(stats['by_payment']?['card'])} so\'m', Icons.credit_card, const Color(0xFF3B82F6)),
            _StatCard('Online', '${_fmt((double.tryParse(stats['by_payment']?['click']?.toString() ?? '0') ?? 0) + (double.tryParse(stats['by_payment']?['payme']?.toString() ?? '0') ?? 0))} so\'m', Icons.phone_android, const Color(0xFF6366F1)),
          ],
        ),
      ],
    );
  }
}

class _StatCard extends StatelessWidget {
  final String label;
  final String value;
  final IconData icon;
  final Color color;
  const _StatCard(this.label, this.value, this.icon, this.color);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: color.withOpacity(0.08),
        borderRadius: BorderRadius.circular(10),
        border: Border.all(color: color.withOpacity(0.2)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(icon, color: color, size: 16),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: color), maxLines: 1, overflow: TextOverflow.ellipsis),
          Text(label, style: const TextStyle(fontSize: 9, color: Color(0xFF6B7280))),
        ],
      ),
    );
  }
}
