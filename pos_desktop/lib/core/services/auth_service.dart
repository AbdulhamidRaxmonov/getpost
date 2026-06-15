import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';

import 'api_service.dart';

class AuthService {
  final SharedPreferences _prefs;
  final ApiService _api;

  AuthService({required SharedPreferences prefs, required ApiService api})
      : _prefs = prefs,
        _api = api;

  Future<Map<String, dynamic>?> pinLogin({
    required int terminalId,
    required String pin,
  }) async {
    final result = await _api.pinLogin(terminalId: terminalId, pin: pin);
    if (result != null) {
      await _prefs.setString('auth_token', result['token']);
      await _prefs.setString('auth_user', jsonEncode(result['user']));

      // Save terminal info
      if (result['terminal'] != null) {
        final terminal = result['terminal'];
        final branch = terminal['branch'];
        final org = branch['organization'];

        await _prefs.setInt('terminal_id', terminal['id']);
        await _prefs.setString('terminal_name', terminal['name']);
        await _prefs.setInt('branch_id', branch['id']);
        await _prefs.setString('branch_name', branch['name']);
        await _prefs.setInt('organization_id', org['id']);
        await _prefs.setString('organization_name', org['name']);
      }
    }
    return result;
  }

  String? getToken() => _prefs.getString('auth_token');

  Map<String, dynamic>? getUser() {
    final userStr = _prefs.getString('auth_user');
    if (userStr == null) return null;
    try {
      return jsonDecode(userStr);
    } catch (_) {
      return null;
    }
  }

  void logout() {
    _prefs.remove('auth_token');
    _prefs.remove('auth_user');
    _prefs.remove('shift_id');
    _prefs.setBool('shift_open', false);
  }

  bool isLoggedIn() => getToken() != null;
}
