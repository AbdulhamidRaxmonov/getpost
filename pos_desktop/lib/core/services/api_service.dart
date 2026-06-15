import 'package:dio/dio.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static const String defaultBaseUrl = 'http://127.0.0.1:8000/api';

  late Dio _dio;
  final SharedPreferences _prefs;

  ApiService({required SharedPreferences prefs}) : _prefs = prefs {
    _initDio();
  }

  // SharedPreferences dagi URL ni o'qib Dio ni qayta yaratish
  void _initDio() {
    final savedUrl = _prefs.getString('api_url') ?? '';
    final baseUrl = savedUrl.isNotEmpty ? savedUrl : defaultBaseUrl;

    print('[API] Base URL: $baseUrl');

    _dio = Dio(BaseOptions(
      baseUrl: baseUrl,
      connectTimeout: const Duration(seconds: 15),
      receiveTimeout: const Duration(seconds: 20),
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
      },
    ));

    _dio.interceptors.clear();
    _dio.interceptors.add(
      InterceptorsWrapper(
        onRequest: (options, handler) {
          final token = _prefs.getString('auth_token');
          if (token != null) {
            options.headers['Authorization'] = 'Bearer $token';
          }
          print('[API →] ${options.method} ${options.uri}');
          handler.next(options);
        },
        onResponse: (response, handler) {
          print('[API ←] ${response.statusCode} ${response.requestOptions.path}');
          handler.next(response);
        },
        onError: (error, handler) {
          print('[API ✗] ${error.response?.statusCode} '
              '${error.requestOptions.path}: ${error.message}');
          if (error.response?.data != null) {
            print('[API ✗] Body: ${error.response?.data}');
          }
          handler.next(error);
        },
      ),
    );
  }

  // URL o'zgarganda Dio ni yangilash
  void updateBaseUrl(String url) {
    _prefs.setString('api_url', url);
    _initDio();
    print('[API] URL updated to: $url');
  }

  // ─── Auth ──────────────────────────────────────────────────────────────────
  Future<Map<String, dynamic>?> pinLogin({
    required int terminalId,
    required String pin,
  }) async {
    // Har safar login qilishdan avval URL ni yangilash
    _initDio();

    try {
      final response = await _dio.post('/auth/pin-login', data: {
        'terminal_id': terminalId,
        'pin': pin,
      });

      print('[PIN LOGIN] Status: ${response.statusCode}');
      print('[PIN LOGIN] Data keys: ${(response.data as Map?)?.keys.toList()}');

      if (response.statusCode == 200 && response.data != null) {
        final token = response.data['token'] as String?;
        if (token != null) {
          await _prefs.setString('auth_token', token);
        }
        return Map<String, dynamic>.from(response.data as Map);
      }
      return null;
    } on DioException catch (e) {
      print('[PIN LOGIN ERROR] ${e.type}: ${e.message}');
      print('[PIN LOGIN ERROR] Status: ${e.response?.statusCode}');
      print('[PIN LOGIN ERROR] Data: ${e.response?.data}');

      switch (e.type) {
        case DioExceptionType.connectionTimeout:
        case DioExceptionType.receiveTimeout:
        case DioExceptionType.sendTimeout:
          throw Exception(
              'Server javob bermayapti. Laravel ishga tushirilganmi?\n'
              'php artisan serve');

        case DioExceptionType.connectionError:
          final url = _prefs.getString('api_url') ?? defaultBaseUrl;
          throw Exception(
              'Serverga ulanib bo\'lmadi.\n'
              'URL: $url\n'
              'Laravel ishga tushirilganmi?');

        default:
          final status = e.response?.statusCode;
          if (status == 401) throw Exception('PIN kod noto\'g\'ri');
          if (status == 404) throw Exception('Terminal topilmadi (ID: $terminalId)');
          if (status == 422) {
            final errors = e.response?.data?['errors'];
            throw Exception('Ma\'lumot xato: $errors');
          }
          throw Exception(
              'Xatolik: ${e.response?.data?['message'] ?? e.message}');
      }
    }
  }

  // ─── Products ──────────────────────────────────────────────────────────────
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
      final response = await _dio.get('/products/barcode',
          queryParameters: {'barcode': barcode});
      return response.data;
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) return null;
      rethrow;
    }
  }

  // ─── Categories ────────────────────────────────────────────────────────────
  Future<List<dynamic>> getCategories() async {
    try {
      final response = await _dio.get('/categories');
      return response.data as List;
    } catch (e) {
      throw Exception('Kategoriyalar yuklanmadi: $e');
    }
  }

  // ─── Shifts ────────────────────────────────────────────────────────────────
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
    return Map<String, dynamic>.from(response.data as Map);
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
    return Map<String, dynamic>.from(response.data as Map);
  }

  Future<Map<String, dynamic>> getCurrentShift(int terminalId) async {
    final response = await _dio.get('/shifts/current',
        queryParameters: {'terminal_id': terminalId});
    return Map<String, dynamic>.from(response.data as Map);
  }

  Future<Map<String, dynamic>> getShiftReport(int shiftId) async {
    final response = await _dio.get('/shifts/$shiftId/report');
    return Map<String, dynamic>.from(response.data as Map);
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
    return Map<String, dynamic>.from(response.data as Map);
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
    return Map<String, dynamic>.from(response.data as Map);
  }

  // ─── Orders ────────────────────────────────────────────────────────────────
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
    return Map<String, dynamic>.from(response.data as Map);
  }

  Future<Map<String, dynamic>> getOrder(int orderId) async {
    final response = await _dio.get('/orders/$orderId/receipt');
    return Map<String, dynamic>.from(response.data as Map);
  }

  Future<Map<String, dynamic>> returnOrder(int orderId) async {
    final response = await _dio.post('/orders/$orderId/return');
    return Map<String, dynamic>.from(response.data as Map);
  }

  // ─── Customers ─────────────────────────────────────────────────────────────
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
    return Map<String, dynamic>.from(response.data as Map);
  }
}
