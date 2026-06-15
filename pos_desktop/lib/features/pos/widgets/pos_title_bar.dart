import 'package:flutter/material.dart';
import 'package:window_manager/window_manager.dart';

import '../../../core/theme/app_theme.dart';

class PosTitleBar extends StatelessWidget {
  final String cashierName;
  final String branchName;
  final String terminalName;
  final int? shiftId;
  final VoidCallback onLogout;
  final VoidCallback onCloseShift;

  const PosTitleBar({
    super.key,
    required this.cashierName,
    required this.branchName,
    required this.terminalName,
    this.shiftId,
    required this.onLogout,
    required this.onCloseShift,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onPanStart: (_) => windowManager.startDragging(),
      child: Container(
        height: 44,
        color: Colors.white,
        decoration: const BoxDecoration(
          border: Border(bottom: BorderSide(color: Color(0xFFE5E7EB))),
        ),
        child: Row(
          children: [
            // Logo
            Container(
              width: 44,
              height: 44,
              color: AppTheme.primaryDark,
              child: const Center(
                child: Text(
                  'Y',
                  style: TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 20,
                  ),
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 12),
              child: Text(
                'YesPOS',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 15,
                  color: Color(0xFF1F2937),
                ),
              ),
            ),

            // Navigation tabs
            _NavTab(icon: Icons.home_outlined, label: 'Bosh sahifa'),
            _NavTab(icon: Icons.point_of_sale_outlined, label: 'Kassa', isActive: true),
            _NavTab(icon: Icons.inventory_2_outlined, label: 'Mahsulotlar'),
            _NavTab(icon: Icons.people_outlined, label: 'Mijozlar'),
            _NavTab(icon: Icons.bar_chart_outlined, label: 'Hisobot'),

            const Spacer(),

            // User info
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              margin: const EdgeInsets.symmetric(horizontal: 4),
              decoration: BoxDecoration(
                color: const Color(0xFFF3F4F6),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Row(
                children: [
                  Container(
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: AppTheme.primaryBlue,
                      borderRadius: BorderRadius.circular(6),
                    ),
                    child: Center(
                      child: Text(
                        cashierName.isNotEmpty ? cashierName[0].toUpperCase() : 'K',
                        style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Column(
                    mainAxisSize: MainAxisSize.min,
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(cashierName, style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600)),
                      Text(terminalName, style: const TextStyle(fontSize: 10, color: Color(0xFF6B7280))),
                    ],
                  ),
                ],
              ),
            ),

            // Sync indicator
            Container(
              margin: const EdgeInsets.only(right: 4),
              width: 8,
              height: 8,
              decoration: const BoxDecoration(
                color: Color(0xFFEF4444),
                shape: BoxShape.circle,
              ),
            ),

            // Refresh
            IconButton(
              icon: const Icon(Icons.refresh, size: 18),
              onPressed: () {},
              color: const Color(0xFF6B7280),
              tooltip: 'Yangilash',
            ),

            // Menu
            PopupMenuButton<String>(
              icon: const Icon(Icons.menu, size: 18, color: Color(0xFF6B7280)),
              onSelected: (value) {
                if (value == 'logout') onLogout();
                if (value == 'close_shift') onCloseShift();
              },
              itemBuilder: (_) => [
                const PopupMenuItem(value: 'close_shift', child: Row(
                  children: [Icon(Icons.lock_clock, size: 16), SizedBox(width: 8), Text('Smenani yopish')],
                )),
                const PopupMenuDivider(),
                const PopupMenuItem(value: 'logout', child: Row(
                  children: [Icon(Icons.logout, size: 16, color: Colors.red), SizedBox(width: 8), Text('Chiqish', style: TextStyle(color: Colors.red))],
                )),
              ],
            ),

            // Window controls
            _WinBtn(icon: Icons.minimize, onTap: () => windowManager.minimize()),
            _WinBtn(icon: Icons.crop_square, onTap: () => windowManager.maximize()),
            _WinBtn(icon: Icons.close, onTap: () => windowManager.close(), isClose: true),
          ],
        ),
      ),
    );
  }
}

class _NavTab extends StatelessWidget {
  final IconData icon;
  final String label;
  final bool isActive;
  const _NavTab({required this.icon, required this.label, this.isActive = false});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      decoration: BoxDecoration(
        border: Border(
          bottom: BorderSide(
            color: isActive ? AppTheme.primaryBlue : Colors.transparent,
            width: 2,
          ),
        ),
      ),
      child: Row(
        children: [
          Icon(icon, size: 16, color: isActive ? AppTheme.primaryBlue : const Color(0xFF6B7280)),
          const SizedBox(width: 5),
          Text(
            label,
            style: TextStyle(
              fontSize: 12,
              color: isActive ? AppTheme.primaryBlue : const Color(0xFF6B7280),
              fontWeight: isActive ? FontWeight.w600 : FontWeight.normal,
            ),
          ),
        ],
      ),
    );
  }
}

class _WinBtn extends StatelessWidget {
  final IconData icon;
  final VoidCallback onTap;
  final bool isClose;
  const _WinBtn({required this.icon, required this.onTap, this.isClose = false});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Container(
        width: 40,
        height: 44,
        color: Colors.transparent,
        child: Icon(icon, size: 16,
            color: isClose ? Colors.red.shade400 : const Color(0xFF9CA3AF)),
      ),
    );
  }
}
