(function() {
  var Dispatcher, Javie, jQuery, root, setup_button_group, setup_helper, setup_pagination, setup_switcher;

  root = this;

  Javie = root.Javie;

  jQuery = root.jQuery;

  Dispatcher = Javie.make('event');

  setup_button_group = function($) {
    var buttons, form, group, hidden, name;
    group = $(this);
    form = group.parents('form').eq(0);
    name = group.attr('data-toggle-name');
    hidden = $("input[name='" + name + "']", form);
    buttons = $('button', group);
    buttons.each(function(i, item) {
      var button, set_active;
      button = $(item);
      set_active = function() {
        if (button.val() === hidden.val()) {
          button.addClass('active');
        }
        return true;
      };
      button.on('click', function() {
        buttons.removeClass('active');
        hidden.val($(this).val());
        return set_active();
      });
      return set_active();
    });
    return true;
  };

  setup_helper = function($) {
    $('input[type="date"]').datepicker({
      dateFormat: "yy-mm-dd"
    });
    $('select.form-control[role!="switcher"], .navbar-form > select[role!="switcher"]').select2().removeClass('form-control');
    $('*[role="tooltip"]').tooltip();
    return true;
  };

  setup_pagination = function($) {
    $('div.pagination > ul').each(function(i, item) {
      $(item).addClass('pagination').parent().removeClass('pagination');
      return true;
    });
    return true;
  };

  setup_switcher = function($) {
    var switchers;
    switchers = $('select[role="switcher"]');
    switchers.removeClass('form-control');
    switchers.each(function(i, item) {
      var switcher;
      switcher = $(item);
      switcher.toggleSwitch({
        highlight: switcher.data("highlight"),
        width: 25,
        change: function(e, target) {
          Dispatcher.fire('switcher.change', [switcher, e]);
          return true;
        }
      });
      switcher.css('display', 'none');
      return true;
    });
    return true;
  };

  jQuery(function($) {
    setup_switcher($);
    setup_button_group($);
    setup_helper($);
    setup_pagination($);
    return true;
  });

}).call(this);
