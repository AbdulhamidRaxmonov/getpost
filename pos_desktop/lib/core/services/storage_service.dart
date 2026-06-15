import 'package:shared_preferences/shared_preferences.dart';

class StorageService {
  final SharedPreferences _prefs;
  StorageService({required SharedPreferences prefs}) : _prefs = prefs;

  // Terminal settings
  int? get terminalId => _prefs.getInt('terminal_id');
  String get terminalName => _prefs.getString('terminal_name') ?? 'Kassa-1';
  int? get branchId => _prefs.getInt('branch_id');
  String get branchName => _prefs.getString('branch_name') ?? '';
  int? get organizationId => _prefs.getInt('organization_id');
  String get organizationName => _prefs.getString('organization_name') ?? '';

  // Shift
  int? get shiftId => _prefs.getInt('shift_id');
  bool get isShiftOpen => _prefs.getBool('shift_open') ?? false;
  double get openingCash => _prefs.getDouble('opening_cash') ?? 0;

  // API URL
  String get apiUrl => _prefs.getString('api_url') ?? 'http://localhost:8000/api';
  Future<void> setApiUrl(String url) => _prefs.setString('api_url', url);

  // Terminal ID setup
  Future<void> setTerminalId(int id) => _prefs.setInt('pos_terminal_id', id);
  int? get posTerminalId => _prefs.getInt('pos_terminal_id');
}
