import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../models/product_model.dart';
import '../../../core/providers/providers.dart';

// ─── Products ───────────────────────────────────────────────
final productsProvider = FutureProvider.family<List<ProductModel>, Map<String, dynamic>>((ref, params) async {
  final api = ref.read(apiServiceProvider);
  final session = ref.read(posSessionProvider);
  final data = await api.getProducts(
    search: params['search'] as String?,
    categoryId: params['category_id'] as int?,
    branchId: session?.branchId,
  );
  return data.map((e) => ProductModel.fromJson(e as Map<String, dynamic>)).toList();
});

// ─── Categories ─────────────────────────────────────────────
final categoriesProvider = FutureProvider<List<CategoryModel>>((ref) async {
  final api = ref.read(apiServiceProvider);
  final data = await api.getCategories();
  return data.map((e) => CategoryModel.fromJson(e as Map<String, dynamic>)).toList();
});

// ─── Cart State ─────────────────────────────────────────────
final cartProvider = StateNotifierProvider<CartNotifier, CartState>((ref) {
  return CartNotifier();
});

class CartState {
  final List<CartItem> items;
  final int? selectedIndex;
  final CustomerModel? customer;
  final int checkNumber;

  const CartState({
    this.items = const [],
    this.selectedIndex,
    this.customer,
    this.checkNumber = 1,
  });

  double get subtotal => items.fold(0, (s, i) => s + i.lineTotal);
  double get discountTotal => items.fold(0, (s, i) => s + i.discountAmount);
  double get vatTotal => items.fold(0, (s, i) => s + i.vatAmount);
  double get total => items.fold(0, (s, i) => s + i.totalAmount);
  int get itemCount => items.length;
  bool get isEmpty => items.isEmpty;

  CartState copyWith({
    List<CartItem>? items,
    int? selectedIndex,
    CustomerModel? customer,
    bool clearCustomer = false,
    bool clearSelected = false,
    int? checkNumber,
  }) {
    return CartState(
      items: items ?? this.items,
      selectedIndex: clearSelected ? null : selectedIndex ?? this.selectedIndex,
      customer: clearCustomer ? null : customer ?? this.customer,
      checkNumber: checkNumber ?? this.checkNumber,
    );
  }
}

class CartNotifier extends StateNotifier<CartState> {
  CartNotifier() : super(const CartState());

  void addProduct(ProductModel product) {
    final existingIndex = state.items.indexWhere((item) => item.product.id == product.id);
    if (existingIndex >= 0) {
      final updatedItems = List<CartItem>.from(state.items);
      updatedItems[existingIndex] = updatedItems[existingIndex].copyWith(
        quantity: updatedItems[existingIndex].quantity + 1,
      );
      state = state.copyWith(items: updatedItems, selectedIndex: existingIndex);
    } else {
      final newItems = [...state.items, CartItem(product: product, unitPrice: product.sellingPrice)];
      state = state.copyWith(items: newItems, selectedIndex: newItems.length - 1);
    }
  }

  void updateQuantity(int index, double quantity) {
    if (index < 0 || index >= state.items.length) return;
    if (quantity <= 0) {
      removeItem(index);
      return;
    }
    final updatedItems = List<CartItem>.from(state.items);
    updatedItems[index] = updatedItems[index].copyWith(quantity: quantity);
    state = state.copyWith(items: updatedItems);
  }

  void updatePrice(int index, double price) {
    if (index < 0 || index >= state.items.length) return;
    final updatedItems = List<CartItem>.from(state.items);
    updatedItems[index] = updatedItems[index].copyWith(unitPrice: price);
    state = state.copyWith(items: updatedItems);
  }

  void updateDiscount(int index, double discountPercent) {
    if (index < 0 || index >= state.items.length) return;
    final updatedItems = List<CartItem>.from(state.items);
    updatedItems[index] = updatedItems[index].copyWith(discountPercent: discountPercent);
    state = state.copyWith(items: updatedItems);
  }

  void removeItem(int index) {
    final updatedItems = List<CartItem>.from(state.items)..removeAt(index);
    final newSelected = updatedItems.isEmpty ? null :
        index >= updatedItems.length ? updatedItems.length - 1 : index;
    state = state.copyWith(items: updatedItems, selectedIndex: newSelected, clearSelected: newSelected == null);
  }

  void selectItem(int index) {
    state = state.copyWith(selectedIndex: index);
  }

  void setCustomer(CustomerModel? customer) {
    state = state.copyWith(customer: customer, clearCustomer: customer == null);
  }

  void clear({int nextCheckNumber = 1}) {
    state = CartState(checkNumber: nextCheckNumber);
  }

  void incrementSelected() {
    if (state.selectedIndex != null) {
      final item = state.items[state.selectedIndex!];
      updateQuantity(state.selectedIndex!, item.quantity + 1);
    }
  }

  void decrementSelected() {
    if (state.selectedIndex != null) {
      final item = state.items[state.selectedIndex!];
      updateQuantity(state.selectedIndex!, item.quantity - 1);
    }
  }
}

// ─── Search / Filter ────────────────────────────────────────
final searchQueryProvider = StateProvider<String>((ref) => '');
final selectedCategoryProvider = StateProvider<int?>((ref) => null);

// ─── Customers ──────────────────────────────────────────────
final customersProvider = FutureProvider.family<List<CustomerModel>, String?>((ref, search) async {
  final api = ref.watch(apiServiceProvider);
  final data = await api.getCustomers(search: search);
  return data.map((e) => CustomerModel.fromJson(e as Map<String, dynamic>)).toList();
});

// ─── Filtered products (using search + category) ─────────────
final filteredProductsProvider = Provider<AsyncValue<List<ProductModel>>>((ref) {
  final search = ref.watch(searchQueryProvider);
  final categoryId = ref.watch(selectedCategoryProvider);
  return ref.watch(productsProvider({'search': search, 'category_id': categoryId}));
});
