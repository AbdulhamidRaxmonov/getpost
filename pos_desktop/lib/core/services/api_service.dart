import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String defaultBaseUrl = 'http://127.0.0.1:8000/api';

  late final Dio _dio;
  final SharedPreferences _prefs;

  ApiService({required SharedPreferences prefs}) : _prefs = prefs {
    // SharedPreferences dan URL olish, yo'q bo'lsa default
    final savedUrl = prefs.getString('api_url');
    final baseUrl = (savedUrl != null && savedUrl.isNotEmpty)
        ? savedUrl
        : defaultBaseUrl;

    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 10),
      receiveTimeout: const Duration(seconds: 15),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = _prefs.getString('auth_token');
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          print('[API] ${options.method} ${options.uri}');
          handler.next(options);
        },
        onResponse: (response, handler) {
          print('[API] ${response.statusCode} ${response.requestOptions.path}');
          handler.next(response);
        },
        onError: (error, handler) {
          print('[API ERROR] ${error.response?.statusCode} '
              '${error.requestOptions.path}: ${error.message}');
          handler.next(error);
        },
      ),
    );
  }

  // Auth endpoints
  Future<Map<String, dynamic>?> pinLogin({
    required int terminalId,
    required String pin,
  }) async {
    try {
      final response = await _dio.post('/auth/pin-login', data: {
        'terminal_id': terminalId,
        'pin': pin,
      });

      // Debug: javobni ko'rish
      print('=== PIN LOGIN RESPONSE ===');
      print('Status: ${response.statusCode}');
      print('Data: ${response.data}');
      print('=========================');

      if (response.statusCode == 200 && response.data != null) {
        // Token ni darhol saqlash
        final token = response.data['token'] as String?;
        if (token != null) {
          await _prefs.setString('auth_token', token);
          print('Token saved: $token');
        }
        return response.data as Map<String, dynamic>;
      }
      return null;
    } on DioException catch (e) {
      print('=== PIN LOGIN ERROR ===');
      print('Status: ${e.response?.statusCode}');
      print('Data: ${e.response?.data}');
      print('Message: ${e.message}');
      print('======================');

      if (e.response?.statusCode == 401) {
        throw Exception('PIN kod noto\'g\'ri');
      }
      if (e.response?.statusCode == 404) {
        throw Exception('Terminal topilmadi. Terminal ID ni tekshiring.');
      }
      throw Exception('Server bilan ulanishda xatolik: ${e.message}');
    }
  }

  // Products
  Future<List<dynamic>> getProducts({
    String? search,
    int? categoryId,
    int? branchId,
  }) async {
    try {
      final params = <String, dynamic>{};
      if (search != null && search.isNotEmpty) params['search'] = search;
      if (categoryId != null) params['category_id'] = categoryId;
      if (branchId != null) params['branch_id'] = branchId;

      final response = await _dio.get('/products', queryParameters: params);
      return response.data as List;
    } catch (e) {
      throw Exception('Mahsulotlar yuklanmadi: $e');
    }
  }

  Future<Map<String, dynamic>?> getProductByBarcode(String barcode) async {
    try {
      final response = await _dio.get('/products/barcode', queryParameters: {'barcode': barcode});
      return response.data;
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) return null;
      rethrow;
    }
  }

  // Categories
  Future<List<dynamic>> getCategories() async {
    try {
      final response = await _dio.get('/categories');
      return response.data as List;
    } catch (e) {
      throw Exception('Kategoriyalar yuklanmadi: $e');
    }
  }

  // Shifts
  Future<Map<String, dynamic>> openShift({
    required int terminalId,
    required int branchId,
    required double openingCash,
  }) async {
    final response = await _dio.post('/shifts/open', data: {
      'terminal_id': terminalId,
      'branch_id': branchId,
      'opening_cash': openingCash,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> closeShift({
    required int shiftId,
    required double closingCash,
    String? closingNote,
  }) async {
    final response = await _dio.post('/shifts/$shiftId/close', data: {
      'closing_cash': closingCash,
      'closing_note': closingNote,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> getCurrentShift(int terminalId) async {
    final response = await _dio.get('/shifts/current', queryParameters: {
      'terminal_id': terminalId,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> getShiftReport(int shiftId) async {
    final response = await _dio.get('/shifts/$shiftId/report');
    return response.data;
  }

  Future<Map<String, dynamic>> cashIn({
    required int shiftId,
    required double amount,
    String? reason,
  }) async {
    final response = await _dio.post('/shifts/cash-in', data: {
      'shift_id': shiftId,
      'amount': amount,
      'reason': reason,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> cashOut({
    required int shiftId,
    required double amount,
    String? reason,
  }) async {
    final response = await _dio.post('/shifts/cash-out', data: {
      'shift_id': shiftId,
      'amount': amount,
      'reason': reason,
    });
    return response.data;
  }

  // Orders
  Future<Map<String, dynamic>> createOrder({
    required int terminalId,
    required int shiftId,
    required List<Map<String, dynamic>> items,
    required String paymentMethod,
    required double paidAmount,
    int? customerId,
    Map<String, dynamic>? paymentDetails,
    String? note,
  }) async {
    final response = await _dio.post('/orders', data: {
      'terminal_id': terminalId,
      'shift_id': shiftId,
      'items': items,
      'payment_method': paymentMethod,
      'paid_amount': paidAmount,
      'customer_id': customerId,
      'payment_details': paymentDetails,
      'note': note,
    });
    return response.data;
  }

  Future<Map<String, dynamic>> getOrder(int orderId) async {
    final response = await _dio.get('/orders/$orderId/receipt');
    return response.data;
  }

  Future<Map<String, dynamic>> returnOrder(int orderId) async {
    final response = await _dio.post('/orders/$orderId/return');
    return response.data;
  }

  // Customers
  Future<List<dynamic>> getCustomers({String? search}) async {
    try {
      final response = await _dio.get('/customers',
          queryParameters: search != null ? {'search': search} : null);
      return response.data as List;
    } catch (e) {
      return [];
    }
  }

  Future<Map<String, dynamic>> createCustomer({
    required String name,
    String? phone,
  }) async {
    final response = await _dio.post('/customers', data: {
      'name': name,
      'phone': phone,
    });
    return response.data;
  }
}
