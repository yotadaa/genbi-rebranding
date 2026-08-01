(function () {
  'use strict';

  const bootstrap = window.GenBISettingsBootstrap || {};
  window.GenBIThemeRegistry = {
    themes: (bootstrap.theme && bootstrap.theme.themes) || [],
  };
})();
