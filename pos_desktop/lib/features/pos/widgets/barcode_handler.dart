import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/providers/providers.dart';
import '../providers/pos_provider.dart';
import '../models/product_model.dart';

/// USB Barcode Scanner handler
/// USB scanner klaviatura kabi ishlaydi — harflarni tez ketma-ket yuboradi
/// va oxirida Enter bosadi. Shu xususiyatdan foydalanamiz.
class BarcodeHandler extends ConsumerStatefulWidget {
  final Widget child;

  const BarcodeHandler({super.key, required this.child});

  @override
  ConsumerState<BarcodeHandler> createState() => _BarcodeHandlerState();
}

class _BarcodeHandlerState extends ConsumerState<BarcodeHandler> {
  final StringBuffer _barcodeBuffer = StringBuffer();
  Timer? _barcodeTimer;
  DateTime? _lastKeyTime;

  // USB scanner odatda 50ms dan tez yozadi
  static const _scannerMaxDelay = Duration(milliseconds: 80);
  // Barcode kamida 3 ta belgi bo'lishi kerak
  static const _minBarcodeLength = 3;

  @override
  void initState() {
    super.initState();
    ServicesBinding.instance.keyboard.addHandler(_handleKey);
  }

  @override
  void dispose() {
    ServicesBinding.instance.keyboard.removeHandler(_handleKey);
    _barcodeTimer?.cancel();
    super.dispose();
  }

  bool _handleKey(KeyEvent event) {
    if (event is! KeyDownEvent) return false;

    final now = DateTime.now();

    // Enter bosilsa — barcode to'liq keldi
    if (event.logicalKey == LogicalKeyboardKey.enter ||
        event.logicalKey == LogicalKeyboardKey.numpadEnter) {
      final barcode = _barcodeBuffer.toString().trim();
      _barcodeBuffer.clear();
      _barcodeTimer?.cancel();

      if (barcode.length >= _minBarcodeLength) {
        _searchByBarcode(barcode);
        return true;
      }
      return false;
    }

    // Raqam va harflarni bufferlash
    final char = _getCharFromKey(event);
    if (char != null) {
      // Agar oldingi belgidan 80ms dan ko'p vaqt o'tgan bo'lsa — yangi input
      if (_lastKeyTime != null &&
          now.difference(_lastKeyTime!) > _scannerMaxDelay) {
        // Odatiy klaviatura teri — scanner emas, reset
        _barcodeBuffer.clear();
      }
      _lastKeyTime = now;
      _barcodeBuffer.write(char);

      // Timeout — agar Enter kelmasdan 200ms o'tsa, bufferni tozala
      _barcodeTimer?.cancel();
      _barcodeTimer = Timer(const Duration(milliseconds: 200), () {
        _barcodeBuffer.clear();
      });

      // Barcode search field da aks ettirilmasligi uchun
      // (faqat scanner tezligida kelgan input uchun)
      return false;
    }

    return false;
  }

  String? _getCharFromKey(KeyEvent event) {
    final key = event.logicalKey;
    // Raqamlar
    final numMap = {
      LogicalKeyboardKey.digit0: '0', LogicalKeyboardKey.digit1: '1',
      LogicalKeyboardKey.digit2: '2', LogicalKeyboardKey.digit3: '3',
      LogicalKeyboardKey.digit4: '4', LogicalKeyboardKey.digit5: '5',
      LogicalKeyboardKey.digit6: '6', LogicalKeyboardKey.digit7: '7',
      LogicalKeyboardKey.digit8: '8', LogicalKeyboardKey.digit9: '9',
      LogicalKeyboardKey.numpad0: '0', LogicalKeyboardKey.numpad1: '1',
      LogicalKeyboardKey.numpad2: '2', LogicalKeyboardKey.numpad3: '3',
      LogicalKeyboardKey.numpad4: '4', LogicalKeyboardKey.numpad5: '5',
      LogicalKeyboardKey.numpad6: '6', LogicalKeyboardKey.numpad7: '7',
      LogicalKeyboardKey.numpad8: '8', LogicalKeyboardKey.numpad9: '9',
    };
    if (numMap.containsKey(key)) return numMap[key];

    // Harflar (EAN barcodelarda ba'zan bo'ladi)
    final keyLabel = key.keyLabel;
    if (keyLabel.length == 1) return keyLabel;

    return null;
  }

  Future<void> _searchByBarcode(String barcode) async {
    if (!mounted) return;

    // Barcode overlay ko'rsatish
    _showScanOverlay(barcode);

    try {
      final api = ref.read(apiServiceProvider);
      final product = await api.getProductByBarcode(barcode);

      if (!mounted) return;

      if (product != null) {
        final productModel = ProductModel.fromJson(product);
        ref.read(cartProvider.notifier).addProduct(productModel);
        _showSuccessSnack(productModel.name);
      } else {
        _showNotFoundSnack(barcode);
      }
    } catch (e) {
      if (mounted) _showErrorSnack(e.toString());
    }
  }

  OverlayEntry? _overlayEntry;

  void _showScanOverlay(String barcode) {
    _overlayEntry?.remove();
    _overlayEntry = OverlayEntry(
      builder: (_) => Positioned(
        top: 60,
        left: 0,
        right: 0,
        child: Center(
          child: Material(
            color: Colors.transparent,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
              decoration: BoxDecoration(
                color: const Color(0xFF1E3A5F),
                borderRadius: BorderRadius.circular(10),
                boxShadow: [
                  BoxShadow(color: Colors.black.withOpacity(0.3), blurRadius: 10),
                ],
              ),
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.qr_code_scanner, color: Colors.white, size: 18),
                  const SizedBox(width: 10),
                  Text(
                    'Skaner: $barcode',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 14,
                      fontWeight: FontWeight.w600,
                    ),
                  ),
                  const SizedBox(width: 10),
                  const SizedBox(
                    width: 14,
                    height: 14,
                    child: CircularProgressIndicator(
                      color: Colors.white,
                      strokeWidth: 2,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );

    if (mounted) {
      Overlay.of(context).insert(_overlayEntry!);
      // 2 soniyadan keyin o'chirish
      Timer(const Duration(seconds: 2), () {
        _overlayEntry?.remove();
        _overlayEntry = null;
      });
    }
  }

  void _showSuccessSnack(String productName) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.check_circle, color: Colors.white, size: 18),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                '✓ $productName savatga qo\'shildi',
                style: const TextStyle(fontWeight: FontWeight.w500),
              ),
            ),
          ],
        ),
        backgroundColor: const Color(0xFF10B981),
        duration: const Duration(seconds: 2),
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(12),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  void _showNotFoundSnack(String barcode) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.warning_amber, color: Colors.white, size: 18),
            const SizedBox(width: 10),
            Text('Mahsulot topilmadi: $barcode'),
          ],
        ),
        backgroundColor: const Color(0xFFF59E0B),
        duration: const Duration(seconds: 3),
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(12),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  void _showErrorSnack(String error) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text('Xatolik: $error'),
        backgroundColor: const Color(0xFFEF4444),
        duration: const Duration(seconds: 3),
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(12),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return widget.child;
  }
}
