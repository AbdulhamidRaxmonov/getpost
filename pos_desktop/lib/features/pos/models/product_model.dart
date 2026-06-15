class ProductModel {
  final int id;
  final int? categoryId;
  final int? unitId;
  final String sku;
  final String? barcode;
  final String name;
  final String? description;
  final String? image;
  final double purchasePrice;
  final double sellingPrice;
  final double minPrice;
  final double vatPercent;
  final bool isActive;
  final bool trackStock;
  final double stockQuantity;
  final CategoryModel? category;
  final UnitModel? unit;

  const ProductModel({
    required this.id,
    this.categoryId,
    this.unitId,
    required this.sku,
    this.barcode,
    required this.name,
    this.description,
    this.image,
    required this.purchasePrice,
    required this.sellingPrice,
    required this.minPrice,
    required this.vatPercent,
    required this.isActive,
    required this.trackStock,
    this.stockQuantity = 0,
    this.category,
    this.unit,
  });

  factory ProductModel.fromJson(Map<String, dynamic> json) {
    return ProductModel(
      id: json['id'],
      categoryId: json['category_id'],
      unitId: json['unit_id'],
      sku: json['sku'] ?? '',
      barcode: json['barcode'],
      name: json['name'] ?? '',
      description: json['description'],
      image: json['image'],
      purchasePrice: double.tryParse(json['purchase_price']?.toString() ?? '0') ?? 0,
      sellingPrice: double.tryParse(json['selling_price']?.toString() ?? '0') ?? 0,
      minPrice: double.tryParse(json['min_price']?.toString() ?? '0') ?? 0,
      vatPercent: double.tryParse(json['vat_percent']?.toString() ?? '0') ?? 0,
      isActive: json['is_active'] == true || json['is_active'] == 1,
      trackStock: json['track_stock'] == true || json['track_stock'] == 1,
      stockQuantity: double.tryParse(json['stock_quantity']?.toString() ?? '0') ?? 0,
      category: json['category'] != null ? CategoryModel.fromJson(json['category']) : null,
      unit: json['unit'] != null ? UnitModel.fromJson(json['unit']) : null,
    );
  }
}

class CategoryModel {
  final int id;
  final String name;
  final String color;
  final String? icon;

  const CategoryModel({
    required this.id,
    required this.name,
    required this.color,
    this.icon,
  });

  factory CategoryModel.fromJson(Map<String, dynamic> json) {
    return CategoryModel(
      id: json['id'],
      name: json['name'] ?? '',
      color: json['color'] ?? '#3B82F6',
      icon: json['icon'],
    );
  }
}

class UnitModel {
  final int id;
  final String name;
  final String shortName;
  final bool isFractional;

  const UnitModel({
    required this.id,
    required this.name,
    required this.shortName,
    required this.isFractional,
  });

  factory UnitModel.fromJson(Map<String, dynamic> json) {
    return UnitModel(
      id: json['id'],
      name: json['name'] ?? '',
      shortName: json['short_name'] ?? '',
      isFractional: json['is_fractional'] == true || json['is_fractional'] == 1,
    );
  }
}

class CartItem {
  final ProductModel product;
  double quantity;
  double unitPrice;
  double discountPercent;

  CartItem({
    required this.product,
    this.quantity = 1,
    required this.unitPrice,
    this.discountPercent = 0,
  });

  double get discountAmount => lineTotal * discountPercent / 100;
  double get lineTotal => quantity * unitPrice;
  double get vatAmount => (lineTotal - discountAmount) * product.vatPercent / 100;
  double get totalAmount => lineTotal - discountAmount + vatAmount;

  CartItem copyWith({
    double? quantity,
    double? unitPrice,
    double? discountPercent,
  }) {
    return CartItem(
      product: product,
      quantity: quantity ?? this.quantity,
      unitPrice: unitPrice ?? this.unitPrice,
      discountPercent: discountPercent ?? this.discountPercent,
    );
  }
}

class CustomerModel {
  final int id;
  final String name;
  final String? phone;
  final double balance;
  final double discountPercent;

  const CustomerModel({
    required this.id,
    required this.name,
    this.phone,
    required this.balance,
    required this.discountPercent,
  });

  factory CustomerModel.fromJson(Map<String, dynamic> json) {
    return CustomerModel(
      id: json['id'],
      name: json['name'] ?? '',
      phone: json['phone'],
      balance: double.tryParse(json['balance']?.toString() ?? '0') ?? 0,
      discountPercent: double.tryParse(json['discount_percent']?.toString() ?? '0') ?? 0,
    );
  }
}
