import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../../core/theme/app_theme.dart';
import '../../../core/providers/providers.dart';
import '../models/product_model.dart';
import '../providers/pos_provider.dart';

// ─── Provider ────────────────────────────────────────────────────────────────
final _customerSearchProvider = StateProvider<String>((ref) => '');

final _customerListProvider =
    FutureProvider.family<List<CustomerModel>, String>((ref, search) async {
  final api = ref.read(apiServiceProvider);
  final data = await api.getCustomers(search: search.isEmpty ? null : search);
  return data
      .map((e) => CustomerModel.fromJson(e as Map<String, dynamic>))
      .toList();
});

// ─── Dialog ───────────────────────────────────────────────────────────────────
class CustomerSelectDialog extends ConsumerStatefulWidget {
  const CustomerSelectDialog({super.key});

  @override
  ConsumerState<CustomerSelectDialog> createState() =>
      _CustomerSelectDialogState();
}

class _CustomerSelectDialogState extends ConsumerState<CustomerSelectDialog>
    with SingleTickerProviderStateMixin {
  final _searchCtrl = TextEditingController();
  final _searchFocus = FocusNode();
  Timer? _debounce;
  late TabController _tabController;
  String _search = '';

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    WidgetsBinding.instance.addPostFrameCallback((_) {
      _searchFocus.requestFocus();
    });
  }

  @override
  void dispose() {
    _searchCtrl.dispose();
    _searchFocus.dispose();
    _debounce?.cancel();
    _tabController.dispose();
    super.dispose();
  }

  void _onSearchChanged(String value) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 400), () {
      setState(() => _search = value);
    });
  }

  void _selectCustomer(CustomerModel customer) {
    ref.read(cartProvider.notifier).setCustomer(customer);
    Navigator.of(context).pop();

    // Balans ko'rsatish
    if (customer.balance < 0) {
      _showDebtWarning(customer);
    }
  }

  void _showDebtWarning(CustomerModel customer) {
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Row(
          children: [
            const Icon(Icons.warning_amber, color: Colors.white, size: 18),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                '${customer.name} ning qarzi: ${_fmt(customer.balance.abs())} so\'m',
                style: const TextStyle(fontWeight: FontWeight.w500),
              ),
            ),
          ],
        ),
        backgroundColor: const Color(0xFFEF4444),
        duration: const Duration(seconds: 4),
        behavior: SnackBarBehavior.floating,
        margin: const EdgeInsets.all(12),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    );
  }

  String _fmt(double v) {
    final s = v.toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(' ');
      b.write(s[i]);
    }
    return b.toString();
  }

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      insetPadding: const EdgeInsets.all(20),
      child: Container(
        width: 520,
        height: 600,
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 30),
          ],
        ),
        child: Column(
          children: [
            // ── Header ────────────────────────────────────────
            _buildHeader(),

            // ── Search ────────────────────────────────────────
            _buildSearch(),

            // ── Tabs ──────────────────────────────────────────
            TabBar(
              controller: _tabController,
              labelColor: AppTheme.primaryBlue,
              unselectedLabelColor: const Color(0xFF6B7280),
              indicatorColor: AppTheme.primaryBlue,
              labelStyle: const TextStyle(
                  fontSize: 13, fontWeight: FontWeight.w600),
              tabs: const [
                Tab(text: 'Mijozlar ro\'yxati'),
                Tab(text: 'Yangi mijoz'),
              ],
            ),

            // ── Tab content ───────────────────────────────────
            Expanded(
              child: TabBarView(
                controller: _tabController,
                children: [
                  _CustomerList(
                    search: _search,
                    onSelect: _selectCustomer,
                  ),
                  _NewCustomerForm(
                    onCreated: _selectCustomer,
                  ),
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
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 16),
      decoration: const BoxDecoration(
        color: Color(0xFFF8FAFC),
        borderRadius: BorderRadius.only(
          topLeft: Radius.circular(16),
          topRight: Radius.circular(16),
        ),
        border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
      ),
      child: Row(
        children: [
          Container(
            width: 36,
            height: 36,
            decoration: BoxDecoration(
              color: AppTheme.primaryBlue.withOpacity(0.1),
              borderRadius: BorderRadius.circular(8),
            ),
            child: const Icon(Icons.people_outlined,
                color: AppTheme.primaryBlue, size: 20),
          ),
          const SizedBox(width: 12),
          const Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Mijoz tanlash',
                    style: TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF1F2937))),
                Text('Mijozni tanlang yoki yangi qo\'shing',
                    style: TextStyle(
                        fontSize: 12, color: Color(0xFF6B7280))),
              ],
            ),
          ),
          IconButton(
            icon: const Icon(Icons.close, size: 20),
            onPressed: () => Navigator.of(context).pop(),
            color: const Color(0xFF6B7280),
          ),
        ],
      ),
    );
  }

  Widget _buildSearch() {
    return Padding(
      padding: const EdgeInsets.all(12),
      child: TextField(
        controller: _searchCtrl,
        focusNode: _searchFocus,
        onChanged: _onSearchChanged,
        style: const TextStyle(fontSize: 13),
        decoration: InputDecoration(
          hintText: 'Ism yoki telefon raqam bo\'yicha qidirish...',
          hintStyle:
              const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF)),
          prefixIcon: const Icon(Icons.search,
              size: 18, color: Color(0xFF9CA3AF)),
          suffixIcon: _searchCtrl.text.isNotEmpty
              ? IconButton(
                  icon: const Icon(Icons.clear, size: 16),
                  onPressed: () {
                    _searchCtrl.clear();
                    setState(() => _search = '');
                  },
                )
              : null,
          border: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
          ),
          enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
          ),
          focusedBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(10),
            borderSide:
                const BorderSide(color: AppTheme.primaryBlue, width: 2),
          ),
          filled: true,
          fillColor: const Color(0xFFF9FAFB),
          contentPadding:
              const EdgeInsets.symmetric(horizontal: 14, vertical: 10),
          isDense: true,
        ),
      ),
    );
  }
}

// ─── Mijozlar ro'yxati ────────────────────────────────────────────────────────
class _CustomerList extends ConsumerWidget {
  final String search;
  final ValueChanged<CustomerModel> onSelect;

  const _CustomerList({required this.search, required this.onSelect});

  String _fmt(double v) {
    final s = v.abs().toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(' ');
      b.write(s[i]);
    }
    return b.toString();
  }

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final customersAsync = ref.watch(_customerListProvider(search));

    return customersAsync.when(
      loading: () =>
          const Center(child: CircularProgressIndicator()),
      error: (e, _) => Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.error_outline,
                size: 40, color: Color(0xFF9CA3AF)),
            const SizedBox(height: 8),
            Text('Yuklanmadi: $e',
                style: const TextStyle(color: Color(0xFF6B7280))),
            TextButton(
              onPressed: () =>
                  ref.refresh(_customerListProvider(search)),
              child: const Text('Qayta urinish'),
            ),
          ],
        ),
      ),
      data: (customers) => customers.isEmpty
          ? Center(
              child: Column(
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  const Icon(Icons.person_off_outlined,
                      size: 48, color: Color(0xFF9CA3AF)),
                  const SizedBox(height: 12),
                  Text(
                    search.isEmpty
                        ? 'Hali mijozlar yo\'q'
                        : '"$search" bo\'yicha topilmadi',
                    style: const TextStyle(color: Color(0xFF6B7280)),
                  ),
                ],
              ),
            )
          : ListView.separated(
              padding: const EdgeInsets.symmetric(vertical: 4),
              itemCount: customers.length,
              separatorBuilder: (_, __) =>
                  const Divider(height: 1, indent: 16, endIndent: 16),
              itemBuilder: (_, i) {
                final c = customers[i];
                final hasDebt = c.balance < 0;
                final hasCredit = c.balance > 0;

                return ListTile(
                  onTap: () => onSelect(c),
                  contentPadding: const EdgeInsets.symmetric(
                      horizontal: 16, vertical: 4),
                  leading: CircleAvatar(
                    radius: 20,
                    backgroundColor: hasDebt
                        ? AppTheme.danger.withOpacity(0.1)
                        : AppTheme.primaryBlue.withOpacity(0.1),
                    child: Text(
                      c.name.isNotEmpty
                          ? c.name[0].toUpperCase()
                          : '?',
                      style: TextStyle(
                        color: hasDebt
                            ? AppTheme.danger
                            : AppTheme.primaryBlue,
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                      ),
                    ),
                  ),
                  title: Row(
                    children: [
                      Expanded(
                        child: Text(
                          c.name,
                          style: const TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: Color(0xFF1F2937),
                          ),
                        ),
                      ),
                      if (c.discountPercent > 0)
                        Container(
                          padding: const EdgeInsets.symmetric(
                              horizontal: 6, vertical: 2),
                          decoration: BoxDecoration(
                            color: AppTheme.success.withOpacity(0.1),
                            borderRadius: BorderRadius.circular(4),
                          ),
                          child: Text(
                            '-${c.discountPercent.toStringAsFixed(0)}%',
                            style: const TextStyle(
                              fontSize: 10,
                              color: AppTheme.success,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ),
                    ],
                  ),
                  subtitle: Row(
                    children: [
                      if (c.phone != null && c.phone!.isNotEmpty) ...[
                        const Icon(Icons.phone,
                            size: 12, color: Color(0xFF9CA3AF)),
                        const SizedBox(width: 4),
                        Text(c.phone!,
                            style: const TextStyle(
                                fontSize: 12,
                                color: Color(0xFF6B7280))),
                        const SizedBox(width: 12),
                      ],
                      // Balans ko'rsatish
                      if (hasDebt) ...[
                        const Icon(Icons.arrow_downward,
                            size: 12, color: AppTheme.danger),
                        const SizedBox(width: 2),
                        Text(
                          'Qarz: ${_fmt(c.balance)} so\'m',
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.danger,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ] else if (hasCredit) ...[
                        const Icon(Icons.arrow_upward,
                            size: 12, color: AppTheme.success),
                        const SizedBox(width: 2),
                        Text(
                          'Balans: ${_fmt(c.balance)} so\'m',
                          style: const TextStyle(
                            fontSize: 12,
                            color: AppTheme.success,
                            fontWeight: FontWeight.w600,
                          ),
                        ),
                      ] else
                        const Text('Qarz yo\'q',
                            style: TextStyle(
                                fontSize: 12,
                                color: Color(0xFF9CA3AF))),
                    ],
                  ),
                  trailing: const Icon(Icons.chevron_right,
                      color: Color(0xFF9CA3AF)),
                );
              },
            ),
    );
  }
}

// ─── Yangi mijoz forma ────────────────────────────────────────────────────────
class _NewCustomerForm extends ConsumerStatefulWidget {
  final ValueChanged<CustomerModel> onCreated;

  const _NewCustomerForm({required this.onCreated});

  @override
  ConsumerState<_NewCustomerForm> createState() => _NewCustomerFormState();
}

class _NewCustomerFormState extends ConsumerState<_NewCustomerForm> {
  final _formKey = GlobalKey<FormState>();
  final _nameCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  final _discountCtrl = TextEditingController(text: '0');
  bool _isLoading = false;
  String _error = '';

  @override
  void dispose() {
    _nameCtrl.dispose();
    _phoneCtrl.dispose();
    _discountCtrl.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _isLoading = true;
      _error = '';
    });

    try {
      final api = ref.read(apiServiceProvider);
      final result = await api.createCustomer(
        name: _nameCtrl.text.trim(),
        phone: _phoneCtrl.text.trim().isEmpty
            ? null
            : _phoneCtrl.text.trim(),
      );

      final customer = CustomerModel.fromJson(result);
      if (mounted) widget.onCreated(customer);
    } catch (e) {
      setState(() {
        _isLoading = false;
        _error = e.toString().replaceAll('Exception: ', '');
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(20),
      child: Form(
        key: _formKey,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Ism
            _buildLabel('Mijoz ismi *'),
            const SizedBox(height: 6),
            TextFormField(
              controller: _nameCtrl,
              autofocus: true,
              style: const TextStyle(fontSize: 14),
              decoration: _inputDec('Masalan: Alisher Karimov'),
              validator: (v) =>
                  v == null || v.trim().isEmpty ? 'Ismni kiriting' : null,
            ),

            const SizedBox(height: 16),

            // Telefon
            _buildLabel('Telefon raqam'),
            const SizedBox(height: 6),
            TextFormField(
              controller: _phoneCtrl,
              keyboardType: TextInputType.phone,
              style: const TextStyle(fontSize: 14),
              decoration: _inputDec('+998901234567'),
            ),

            const SizedBox(height: 16),

            // Chegirma
            _buildLabel('Doimiy chegirma (%)'),
            const SizedBox(height: 6),
            TextFormField(
              controller: _discountCtrl,
              keyboardType:
                  const TextInputType.numberWithOptions(decimal: true),
              style: const TextStyle(fontSize: 14),
              decoration: _inputDec('0'),
              validator: (v) {
                final d = double.tryParse(v ?? '0');
                if (d == null) return 'Noto\'g\'ri qiymat';
                if (d < 0 || d > 100) return '0-100 oralig\'ida bo\'lsin';
                return null;
              },
            ),

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
                    const Icon(Icons.error_outline,
                        color: AppTheme.danger, size: 16),
                    const SizedBox(width: 8),
                    Expanded(
                      child: Text(_error,
                          style: const TextStyle(
                              color: AppTheme.danger, fontSize: 12)),
                    ),
                  ],
                ),
              ),
            ],

            const SizedBox(height: 24),

            SizedBox(
              width: double.infinity,
              height: 46,
              child: ElevatedButton(
                onPressed: _isLoading ? null : _save,
                style: ElevatedButton.styleFrom(
                  backgroundColor: AppTheme.primaryBlue,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(10)),
                  elevation: 0,
                ),
                child: _isLoading
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2),
                      )
                    : const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.person_add, size: 18),
                          SizedBox(width: 8),
                          Text('Mijozni saqlash',
                              style: TextStyle(
                                  fontSize: 14,
                                  fontWeight: FontWeight.w600)),
                        ],
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildLabel(String text) {
    return Text(
      text,
      style: const TextStyle(
          fontSize: 13,
          fontWeight: FontWeight.w500,
          color: Color(0xFF374151)),
    );
  }

  InputDecoration _inputDec(String hint) {
    return InputDecoration(
      hintText: hint,
      hintStyle:
          const TextStyle(fontSize: 13, color: Color(0xFF9CA3AF)),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide: const BorderSide(color: Color(0xFFE5E7EB)),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide:
            const BorderSide(color: AppTheme.primaryBlue, width: 2),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(10),
        borderSide:
            const BorderSide(color: AppTheme.danger, width: 1.5),
      ),
      filled: true,
      fillColor: const Color(0xFFF9FAFB),
      contentPadding:
          const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      isDense: true,
    );
  }
}

// ─── Qarz hisob dialog (mijoz tanlangandan keyin) ─────────────────────────────
class CustomerDebtDialog extends StatelessWidget {
  final CustomerModel customer;

  const CustomerDebtDialog({super.key, required this.customer});

  String _fmt(double v) {
    final s = v.abs().toStringAsFixed(0);
    final b = StringBuffer();
    for (int i = 0; i < s.length; i++) {
      if (i > 0 && (s.length - i) % 3 == 0) b.write(' ');
      b.write(s[i]);
    }
    return b.toString();
  }

  @override
  Widget build(BuildContext context) {
    final hasDebt = customer.balance < 0;

    return Dialog(
      backgroundColor: Colors.transparent,
      child: Container(
        width: 380,
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withOpacity(0.15), blurRadius: 20),
          ],
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            // Avatar
            CircleAvatar(
              radius: 32,
              backgroundColor: hasDebt
                  ? AppTheme.danger.withOpacity(0.1)
                  : AppTheme.success.withOpacity(0.1),
              child: Text(
                customer.name.isNotEmpty
                    ? customer.name[0].toUpperCase()
                    : '?',
                style: TextStyle(
                  fontSize: 28,
                  fontWeight: FontWeight.bold,
                  color: hasDebt ? AppTheme.danger : AppTheme.success,
                ),
              ),
            ),
            const SizedBox(height: 12),

            Text(
              customer.name,
              style: const TextStyle(
                  fontSize: 18, fontWeight: FontWeight.bold),
            ),
            if (customer.phone != null && customer.phone!.isNotEmpty)
              Text(
                customer.phone!,
                style: const TextStyle(
                    fontSize: 13, color: Color(0xFF6B7280)),
              ),

            const SizedBox(height: 20),
            const Divider(),
            const SizedBox(height: 16),

            // Balans
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Hisob holati:',
                    style: TextStyle(fontSize: 14, color: Color(0xFF374151))),
                Container(
                  padding: const EdgeInsets.symmetric(
                      horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                    color: hasDebt
                        ? AppTheme.danger.withOpacity(0.1)
                        : AppTheme.success.withOpacity(0.1),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    hasDebt
                        ? '- ${_fmt(customer.balance)} so\'m QARZ'
                        : '+ ${_fmt(customer.balance)} so\'m BALANS',
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: hasDebt ? AppTheme.danger : AppTheme.success,
                    ),
                  ),
                ),
              ],
            ),

            if (customer.discountPercent > 0) ...[
              const SizedBox(height: 12),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  const Text('Doimiy chegirma:',
                      style: TextStyle(
                          fontSize: 14, color: Color(0xFF374151))),
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: AppTheme.success.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(8),
                    ),
                    child: Text(
                      '${customer.discountPercent.toStringAsFixed(0)}% chegirma',
                      style: const TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.success,
                      ),
                    ),
                  ),
                ],
              ),
            ],

            const SizedBox(height: 24),

            // Buttons
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.of(context).pop(),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      side: const BorderSide(color: Color(0xFFE5E7EB)),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                    child: const Text('Yopish',
                        style: TextStyle(color: Color(0xFF6B7280))),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }
}
