import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../models/product_model.dart';
import '../providers/pos_provider.dart';

class ProductGrid extends ConsumerWidget {
  const ProductGrid({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    // Search va category filter
    final search = ref.watch(searchQueryProvider);
    final categoryId = ref.watch(selectedCategoryProvider);

    // Mahsulotlarni to'g'ridan yuklaymiz
    final productsAsync = ref.watch(
      productsProvider({'search': search, 'category_id': categoryId}),
    );

    final cart = ref.watch(cartProvider);

    return Column(
      children: [
        // ── Cart Table (top half) ─────────────────────────
        if (cart.items.isNotEmpty)
          _CartTable(cartState: cart),

        Expanded(
          child: productsAsync.when(
            loading: () => const Center(child: CircularProgressIndicator()),
            error: (e, _) => Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.wifi_off, size: 48, color: Color(0xFF9CA3AF)),
                  const SizedBox(height: 12),
                  Text(
                    'Mahsulotlar yuklanmadi',
                    style: TextStyle(color: Colors.grey.shade500),
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: () => ref.refresh(
                      productsProvider({'search': search, 'category_id': categoryId}),
                    ),
                    child: const Text('Qayta urinish'),
                  ),
                ],
              ),
            ),
            data: (products) => products.isEmpty
                ? const Center(
                    child: Text(
                      'Mahsulotlar topilmadi',
                      style: TextStyle(color: Color(0xFF9CA3AF)),
                    ),
                  )
                : _ProductTable(products: products),
          ),
        ),
      ],
    );
  }
}

// ─── Cart Table (rasmdagidek chiziqli jadval) ─────────────────────────────────
class _CartTable extends ConsumerWidget {
  final CartState cartState;
  const _CartTable({required this.cartState});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Container(
      constraints: const BoxConstraints(maxHeight: 280),
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB), width: 2)),
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Header
          Container(
            height: 36,
            color: const Color(0xFFF8FAFC),
            child: const Row(
              children: [
                SizedBox(width: 40, child: _HeaderCell('№')),
                Expanded(flex: 5, child: _HeaderCell('Nomi\nSKU / Shtrix Kod')),
                SizedBox(width: 100, child: _HeaderCell('Narxi', align: TextAlign.right)),
                SizedBox(width: 80, child: _HeaderCell('Miqdor', align: TextAlign.right)),
                SizedBox(width: 80, child: _HeaderCell('Chegirma', align: TextAlign.right)),
                SizedBox(width: 100, child: _HeaderCell('Summa', align: TextAlign.right)),
              ],
            ),
          ),
          // Items
          Flexible(
            child: ListView.builder(
              shrinkWrap: true,
              itemCount: cartState.items.length,
              itemBuilder: (ctx, i) {
                final item = cartState.items[i];
                final isSelected = i == cartState.selectedIndex;
                return _CartRow(
                  index: i,
                  item: item,
                  isSelected: isSelected,
                  onTap: () => ref.read(cartProvider.notifier).selectItem(i),
                  onRemove: () => ref.read(cartProvider.notifier).removeItem(i),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _HeaderCell extends StatelessWidget {
  final String text;
  final TextAlign? align;
  const _HeaderCell(this.text, {this.align});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
      child: Text(
        text,
        style: const TextStyle(fontSize: 10, fontWeight: FontWeight.w600, color: Color(0xFF6B7280)),
        textAlign: align ?? TextAlign.left,
        maxLines: 2,
      ),
    );
  }
}

class _CartRow extends ConsumerStatefulWidget {
  final int index;
  final CartItem item;
  final bool isSelected;
  final VoidCallback onTap;
  final VoidCallback onRemove;

  const _CartRow({
    required this.index,
    required this.item,
    required this.isSelected,
    required this.onTap,
    required this.onRemove,
  });

  @override
  ConsumerState<_CartRow> createState() => _CartRowState();
}

class _CartRowState extends ConsumerState<_CartRow> {
  bool _hovering = false;
  late TextEditingController _qtyCtrl;
  late TextEditingController _priceCtrl;

  @override
  void initState() {
    super.initState();
    _qtyCtrl = TextEditingController(text: _formatNum(widget.item.quantity));
    _priceCtrl = TextEditingController(text: _formatNum(widget.item.unitPrice));
  }

  @override
  void didUpdateWidget(_CartRow old) {
    super.didUpdateWidget(old);
    if (old.item.quantity != widget.item.quantity) {
      _qtyCtrl.text = _formatNum(widget.item.quantity);
    }
    if (old.item.unitPrice != widget.item.unitPrice) {
      _priceCtrl.text = _formatNum(widget.item.unitPrice);
    }
  }

  @override
  void dispose() {
    _qtyCtrl.dispose();
    _priceCtrl.dispose();
    super.dispose();
  }

  String _formatNum(double v) => v == v.roundToDouble() ? v.toInt().toString() : v.toStringAsFixed(3);

  @override
  Widget build(BuildContext context) {
    final item = widget.item;
    return MouseRegion(
      onEnter: (_) => setState(() => _hovering = true),
      onExit: (_) => setState(() => _hovering = false),
      child: GestureDetector(
        onTap: widget.onTap,
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 100),
          color: widget.isSelected
              ? const Color(0xFFEFF6FF)
              : _hovering
                  ? const Color(0xFFF9FAFB)
                  : Colors.white,
          child: Row(
            children: [
              // №
              SizedBox(
                width: 40,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 6),
                  child: Text('${widget.index + 1}',
                      style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280))),
                ),
              ),
              // Name + SKU
              Expanded(
                flex: 5,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(item.product.name,
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w500,
                            color: widget.isSelected ? AppTheme.primaryBlue : const Color(0xFF1F2937),
                          ),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis),
                      Text('${item.product.sku}${item.product.barcode != null ? ' / ${item.product.barcode}' : ''}',
                          style: const TextStyle(fontSize: 10, color: Color(0xFF9CA3AF), fontFamily: 'monospace')),
                    ],
                  ),
                ),
              ),
              // Price (editable)
              SizedBox(
                width: 100,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: widget.isSelected
                      ? _EditableCell(
                          controller: _priceCtrl,
                          onSubmit: (v) {
                            final price = double.tryParse(v);
                            if (price != null) ref.read(cartProvider.notifier).updatePrice(widget.index, price);
                          },
                        )
                      : Text(
                          _formatMoney(item.unitPrice),
                          textAlign: TextAlign.right,
                          style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w500),
                        ),
                ),
              ),
              // Qty (editable)
              SizedBox(
                width: 80,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: widget.isSelected
                      ? Row(
                          children: [
                            InkWell(
                              onTap: () => ref.read(cartProvider.notifier).decrementSelected(),
                              child: const Icon(Icons.remove, size: 14, color: AppTheme.primaryBlue),
                            ),
                            Expanded(
                              child: _EditableCell(
                                controller: _qtyCtrl,
                                onSubmit: (v) {
                                  final qty = double.tryParse(v);
                                  if (qty != null) ref.read(cartProvider.notifier).updateQuantity(widget.index, qty);
                                },
                              ),
                            ),
                            InkWell(
                              onTap: () => ref.read(cartProvider.notifier).incrementSelected(),
                              child: const Icon(Icons.add, size: 14, color: AppTheme.primaryBlue),
                            ),
                          ],
                        )
                      : Text(
                          _formatNum(item.quantity),
                          textAlign: TextAlign.right,
                          style: const TextStyle(fontSize: 13),
                        ),
                ),
              ),
              // Discount
              SizedBox(
                width: 80,
                child: Text(
                  '${item.discountPercent.toStringAsFixed(0)}%',
                  textAlign: TextAlign.right,
                  style: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)),
                ),
              ),
              // Total
              SizedBox(
                width: 100,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8),
                  child: Row(
                    mainAxisAlignment: MainAxisAlignment.end,
                    children: [
                      Text(
                        _formatMoney(item.totalAmount),
                        style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600),
                      ),
                      if (_hovering || widget.isSelected)
                        InkWell(
                          onTap: widget.onRemove,
                          child: const Padding(
                            padding: EdgeInsets.only(left: 6),
                            child: Icon(Icons.close, size: 14, color: Color(0xFFEF4444)),
                          ),
                        ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatMoney(double v) {
    final str = v.toStringAsFixed(0);
    final buf = StringBuffer();
    for (int i = 0; i < str.length; i++) {
      if (i > 0 && (str.length - i) % 3 == 0) buf.write(',');
      buf.write(str[i]);
    }
    return buf.toString();
  }
}

class _EditableCell extends StatelessWidget {
  final TextEditingController controller;
  final ValueChanged<String> onSubmit;
  const _EditableCell({required this.controller, required this.onSubmit});

  @override
  Widget build(BuildContext context) {
    return TextField(
      controller: controller,
      textAlign: TextAlign.center,
      style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
      keyboardType: const TextInputType.numberWithOptions(decimal: true),
      onSubmitted: onSubmit,
      decoration: InputDecoration(
        isDense: true,
        contentPadding: const EdgeInsets.symmetric(horizontal: 4, vertical: 4),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(4), borderSide: const BorderSide(color: Color(0xFF3B82F6), width: 1.5)),
        enabledBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(4), borderSide: const BorderSide(color: Color(0xFF3B82F6), width: 1.5)),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(4), borderSide: const BorderSide(color: Color(0xFF1D4ED8), width: 2)),
      ),
    );
  }
}

// ─── Product Table ────────────────────────────────────────────────────────────
class _ProductTable extends ConsumerWidget {
  final List<ProductModel> products;
  const _ProductTable({required this.products});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    return Column(
      children: [
        // Table header
        Container(
          height: 32,
          color: const Color(0xFFF8FAFC),
          child: const Row(
            children: [
              SizedBox(width: 70, child: _ProductHeaderCell('Artikul')),
              Expanded(child: _ProductHeaderCell('Mahsulot nomi')),
              SizedBox(width: 110, child: _ProductHeaderCell('Narx', right: true)),
              SizedBox(width: 110, child: _ProductHeaderCell('Qoldiq', right: true)),
            ],
          ),
        ),
        const Divider(height: 1),
        // Table rows
        Expanded(
          child: ListView.separated(
            itemCount: products.length,
            separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF3F4F6)),
            itemBuilder: (ctx, i) => _ProductRow(product: products[i]),
          ),
        ),
      ],
    );
  }
}

class _ProductHeaderCell extends StatelessWidget {
  final String text;
  final bool right;
  const _ProductHeaderCell(this.text, {this.right = false});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: Text(
        text,
        style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF6B7280)),
        textAlign: right ? TextAlign.right : TextAlign.left,
      ),
    );
  }
}

class _ProductRow extends ConsumerStatefulWidget {
  final ProductModel product;
  const _ProductRow({required this.product});

  @override
  ConsumerState<_ProductRow> createState() => _ProductRowState();
}

class _ProductRowState extends ConsumerState<_ProductRow> {
  bool _hovering = false;

  @override
  Widget build(BuildContext context) {
    final product = widget.product;
    final isLowStock = product.trackStock && product.stockQuantity <= 0;

    return MouseRegion(
      onEnter: (_) => setState(() => _hovering = true),
      onExit: (_) => setState(() => _hovering = false),
      child: GestureDetector(
        onTap: () => ref.read(cartProvider.notifier).addProduct(product),
        child: AnimatedContainer(
          duration: const Duration(milliseconds: 100),
          color: _hovering ? const Color(0xFFEFF6FF) : Colors.white,
          padding: const EdgeInsets.symmetric(horizontal: 0, vertical: 6),
          child: Row(
            children: [
              // SKU
              SizedBox(
                width: 70,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text(product.sku,
                      style: const TextStyle(fontSize: 12, fontFamily: 'monospace', color: Color(0xFF6B7280))),
                ),
              ),
              // Name
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text(
                    product.name,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w500,
                      color: isLowStock ? const Color(0xFFEF4444) : const Color(0xFF1F2937),
                    ),
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
              ),
              // Price
              SizedBox(
                width: 110,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text(
                    _formatMoney(product.sellingPrice),
                    textAlign: TextAlign.right,
                    style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w600, color: Color(0xFF1F2937)),
                  ),
                ),
              ),
              // Stock
              SizedBox(
                width: 110,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 12),
                  child: Text(
                    product.trackStock ? _formatQty(product.stockQuantity) : '∞',
                    textAlign: TextAlign.right,
                    style: TextStyle(
                      fontSize: 12,
                      color: isLowStock ? const Color(0xFFEF4444) : const Color(0xFF6B7280),
                      fontWeight: isLowStock ? FontWeight.w600 : FontWeight.normal,
                    ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatMoney(double v) {
    final s = v.toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(',');
      b.write(s[i]);
    }
    return b.toString();
  }

  String _formatQty(double v) => v == v.roundToDouble() ? v.toInt().toString() : v.toStringAsFixed(2);
}
