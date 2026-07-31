(function () {
  'use strict';

  const navItems = window.GenBIData?.navItems;
  if (!Array.isArray(navItems)) return;

  const homeItem = navItems.find((item) => item.key === 'home');
  if (homeItem) homeItem.label = 'Beranda';
}());
