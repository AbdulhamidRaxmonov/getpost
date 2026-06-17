import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';

class SearchBarWidget extends ConsumerStatefulWidget {
  final TextEditingController controller;
  final FocusNode focusNode;
  final ValueChanged<String> onChanged;

  /// Enter bosilganda barcode qidirish (USB scanner uchun)
  final ValueChanged<String>? onBarcodeSearch;

  const SearchBarWidget({
    super.key,
    required this.controller,
    required this.focusNode,
    required this.onChanged,
    this.onBarcodeSearch,
  });

  @override
  ConsumerState<SearchBarWidget> createState() => _SearchBarWidgetState();
}

class _SearchBarWidgetState extends ConsumerState<SearchBarWidget> {
  bool _isScannerMode = false;

  /// Enter bosilganda — agar matn barcode bo'lsa, qidirish
  void _onSubmitted(String value) {
    final trimmed = value.trim();
    if (trimmed.isNotEmpty && widget.onBarcodeSearch != null) {
      widget.onBarcodeSearch!(trimmed);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
      decoration: const BoxDecoration(
        color: Colors.white,
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Row(
        children: [
          // ── Search field ────────────────────────────────────
          Expanded(
            child: Container(
              height: 36,
              decoration: BoxDecoration(
                color: _isScannerMode
                    ? AppTheme.primaryBlue.withOpacity(0.05)
                    : const Color(0xFFF9FAFB),
                borderRadius: BorderRadius.circular(8),
                border: Border.all(
                  color: _isScannerMode
                      ? AppTheme.primaryBlue.withOpacity(0.4)
                      : const Color(0xFFE5E7EB),
                  width: _isScannerMode ? 1.5 : 1,
                ),
              ),
              child: TextField(
                controller: widget.controller,
                focusNode: widget.focusNode,
                onChanged: (v) {
                  widget.onChanged(v);
                  setState(() {});
                },
                onSubmitted: _onSubmitted,
                style: const TextStyle(fontSize: 13),
                decoration: InputDecoration(
                  hintText: _isScannerMode
                      ? 'Barcode skanerlanmoqda...'
                      : 'Mahsulot nomi, SKU yoki barcode...',
                  hintStyle: TextStyle(
                    fontSize: 12,
                    color: _isScannerMode
                        ? AppTheme.primaryBlue.withOpacity(0.6)
                        : const Color(0xFF9CA3AF),
                  ),
                  prefixIcon: Icon(
                    _isScannerMode
                        ? Icons.qr_code_scanner
                        : Icons.search,
                    size: 18,
                    color: _isScannerMode
                        ? AppTheme.primaryBlue
                        : const Color(0xFF9CA3AF),
                  ),
                  suffixIcon: widget.controller.text.isNotEmpty
                      ? IconButton(
                          icon: const Icon(Icons.clear, size: 16),
                          onPressed: () {
                            widget.controller.clear();
                            widget.onChanged('');
                            setState(() {});
                          },
                        )
                      : null,
                  border: InputBorder.none,
                  enabledBorder: InputBorder.none,
                  focusedBorder: InputBorder.none,
                  contentPadding: const EdgeInsets.symmetric(vertical: 8),
                  isDense: true,
                ),
              ),
            ),
          ),

          const SizedBox(width: 8),

          // ── Scanner mode toggle ─────────────────────────────
          Tooltip(
            message: _isScannerMode
                ? 'Scanner rejimi yoqilgan — Enter bilan qidirish'
                : 'Scanner rejimi — barcode qidirish uchun',
            child: InkWell(
              onTap: () {
                setState(() => _isScannerMode = !_isScannerMode);
                if (_isScannerMode) {
                  widget.focusNode.requestFocus();
                }
              },
              borderRadius: BorderRadius.circular(8),
              child: Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                decoration: BoxDecoration(
                  color: _isScannerMode
                      ? AppTheme.primaryBlue
                      : AppTheme.primaryBlue.withOpacity(0.1),
                  borderRadius: BorderRadius.circular(8),
                  border: Border.all(
                    color: _isScannerMode
                        ? AppTheme.primaryBlue
                        : AppTheme.primaryBlue.withOpacity(0.3),
                  ),
                ),
                child: Row(
                  children: [
                    Icon(
                      Icons.qr_code_scanner,
                      size: 15,
                      color: _isScannerMode
                          ? Colors.white
                          : AppTheme.primaryBlue,
                    ),
                    const SizedBox(width: 4),
                    Text(
                      'Skaner',
                      style: TextStyle(
                        fontSize: 11,
                        color: _isScannerMode
                            ? Colors.white
                            : AppTheme.primaryBlue,
                        fontWeight: FontWeight.w500,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
