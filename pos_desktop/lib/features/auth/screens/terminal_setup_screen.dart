import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';

class TerminalSetupScreen extends ConsumerStatefulWidget {
  const TerminalSetupScreen({super.key});

  @override
  ConsumerState<TerminalSetupScreen> createState() => _TerminalSetupScreenState();
}

class _TerminalSetupScreenState extends ConsumerState<TerminalSetupScreen> {
  final _terminalIdController = TextEditingController(text: '1');
  final _apiUrlController = TextEditingController(text: 'http://localhost:8000/api');
  bool _isLoading = false;
  String _error = '';

  @override
  void dispose() {
    _terminalIdController.dispose();
    _apiUrlController.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    final terminalId = int.tryParse(_terminalIdController.text.trim());
    if (terminalId == null) {
      setState(() => _error = 'Terminal ID son bo\'lishi kerak');
      return;
    }

    setState(() { _isLoading = true; _error = ''; });

    try {
      // PosSessionNotifier orqali saqlash
      ref.read(posSessionProvider.notifier).setSession(
        terminalId: terminalId,
        terminalName: 'Kassa-$terminalId',
        branchId: 1,
        branchName: 'Asosiy filial',
        organizationId: 1,
        organizationName: 'YesPOS Demo',
      );

      // API URL ni saqlash
      final prefs = ref.read(sharedPreferencesProvider);
      await prefs.setString('api_url', _apiUrlController.text.trim());

      if (mounted) context.go('/login');
    } catch (e) {
      setState(() { _isLoading = false; _error = e.toString(); });
    }
  }

  @override
  Widget build(BuildContext context) {
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
            width: 440,
            padding: const EdgeInsets.all(32),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(20),
              boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 30)],
            ),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.settings, color: AppTheme.primaryBlue, size: 28),
                    SizedBox(width: 10),
                    Text('Terminal sozlamasi',
                        style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF1F2937))),
                  ],
                ),
                const SizedBox(height: 8),
                Text('POS terminalini birinchi marta sozlash',
                    style: TextStyle(color: Colors.grey.shade500, fontSize: 13)),
                const SizedBox(height: 24),

                _buildField('Server URL', _apiUrlController, hint: 'http://localhost:8000/api'),
                const SizedBox(height: 16),
                _buildField('Terminal ID', _terminalIdController, hint: '1', keyboard: TextInputType.number),

                if (_error.isNotEmpty) ...[
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: AppTheme.danger.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Row(
                      children: [
                        const Icon(Icons.error_outline, color: AppTheme.danger, size: 18),
                        const SizedBox(width: 8),
                        Expanded(child: Text(_error, style: const TextStyle(color: AppTheme.danger, fontSize: 13))),
                      ],
                    ),
                  ),
                ],

                const SizedBox(height: 24),
                SizedBox(
                  width: double.infinity,
                  height: 48,
                  child: ElevatedButton(
                    onPressed: _isLoading ? null : _save,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: AppTheme.primaryBlue,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                    ),
                    child: _isLoading
                        ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Saqlash va davom etish', style: TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: Colors.white)),
                  ),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildField(String label, TextEditingController controller,
      {String? hint, TextInputType keyboard = TextInputType.text}) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500, color: Color(0xFF374151))),
        const SizedBox(height: 6),
        TextField(
          controller: controller,
          keyboardType: keyboard,
          decoration: InputDecoration(
            hintText: hint,
            border: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
            enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: Color(0xFFE5E7EB))),
            focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(10), borderSide: const BorderSide(color: AppTheme.primaryBlue, width: 2)),
            filled: true,
            fillColor: const Color(0xFFF9FAFB),
            contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
          ),
        ),
      ],
    );
  }
}
