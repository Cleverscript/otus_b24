BX.namespace("Gettstart");

BX.Gettstart = {
    getPopup: function (title, content, buttons) {
        return BX.PopupWindowManager.create("popup-gettstart", null, {
            content: content,
            closeIcon: {right: "10px", top: "10px"},
            titleBar: {
                content: BX.create("span", {
                    html: title,
                    'props': {'className': 'popup-gettstart-title-bar'}
                })
            },
            zIndex: 1000,
            offsetLeft: 0,
            offsetTop: 10,
            draggable: {restrict: true},
            closeByEsc: true,
            darkMode: false,
            autoHide: false,
            draggable: true,
            resizable: true,
            min_height: 500,
            min_width: 800,
            lightShadow: true,
            angle: false,
            overlay: {
                backgroundColor: 'black',
                opacity: 800
            },
            buttons: buttons
        });
    },

    timeManWindow: function (type) {
        let timeManWindow = document.getElementById(
            'popup-window-content-timeman_main'
        );

        if (timeManWindow.querySelector('.ui-btn-icon-' + type)) {
            timeManWindow.querySelector('.ui-btn-icon-' + type).click();
        }
    }
}

BX.ready(function () {
    BX.addCustomEvent('onTimeManWindowOpen', () => {
        if (document.getElementById('timeman_main')) {
            // Hide base popup
            document.getElementById('timeman_main').setAttribute(
                'class',
                'timeman_main timeman_main_hide'
            );

            // Show custom popup
            BX.Gettstart.getPopup(
                BX.message('OTUS_GETTSTART_POPUP_TITLE'),
                BX.message('OTUS_GETTSTART_POPUP_CONTENT'),
                [
                    new BX.PopupWindowButton({
                        text: BX.message('OTUS_GETTSTART_POPUP_BTN_START_DAY'),
                        className: "webform-button-link-ok ui-btn ui-btn-success ui-btn-icon-start",
                        events: {
                            click: function () {
                                BX.Gettstart.timeManWindow('start');

                                this.popupWindow.close();
                            }
                        }
                    }),
                    new BX.PopupWindowButton({
                        text: BX.message('OTUS_GETTSTART_POPUP_BTN_FINISH_DAY'),
                        className: "webform-button-link-cancel ui-btn ui-btn-danger ui-btn-icon-stop",
                        events: {
                            click: function () {
                                BX.Gettstart.timeManWindow('stop');

                                this.popupWindow.close();
                            }
                        }
                    }),
                ]
            ).show();
        }
    });
});