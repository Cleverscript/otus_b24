BX.namespace('Bookingfield');

BX.Bookingfield = {
    getPopup: function (procedure_id, title, content, buttons) {
        return new BX.PopupWindow("popup-booking-procedure-" + procedure_id, null, {
            content: content,
            closeIcon: {right: "10px", top: "10px"},
            titleBar: {
                content: BX.create("span", {
                    html: title,
                    'props': {'className': 'popup-booking-procedure-title-bar'}
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
    }
}

BX.ready(function () {
    let procedures = document.querySelectorAll('.procedure-item-grid');

    if (procedures.length > 0)
    {
        for (i = 0; i < procedures.length; i++) {
            if (procedures[i].hasAttribute('data-procedure-id')) {

                let regex = /[\D]{1,}/g;
                let iblock_id = procedures[i].getAttribute('data-iblock-id');;
                let procedure_booking_url = '/services/lists/#IBLOCK_ID#/view/0/';
                let procedure_id = procedures[i].getAttribute('data-procedure-id').replace(regex, '');
                let procedure_name = procedures[i].getAttribute('data-procedure-name');
                let fio = procedures[i].getAttribute('data-fio');

                procedures[i].addEventListener('click', (event) => {
                    event.preventDefault();

                    let content = '<h3>' + procedure_name + '</h3>'
                        + '<p><label><input class="field-fio" type="text" name="fio" value="'+ fio +'"'
                        + ' placeholder="' +  BX.message('OTUS_BOOKINGFIELD_POPUP_FIELD_FIO') + '"/></label></p>'
                        + '<p><label><input class="field-datetime" type="text" name="datetime"'
                        + ' onclick="BX.calendar({node: this, field: this, bTime: true});"'
                        + ' placeholder="' + BX.message('OTUS_BOOKINGFIELD_POPUP_FIELD_DATETIME') + '"/></label></p>'
                        + '<div id="popup-alert-booking-procedure-' + procedure_id + '"></div>';

                    BX.Bookingfield.getPopup(
                        procedure_id,
                        BX.message('OTUS_BOOKINGFIELD_POPUP_TITLE'),
                        content,
                        [
                            new BX.PopupWindowButton({
                                text: BX.message('OTUS_BOOKINGFIELD_POPUP_BTN_CANCEL'),
                                className: "webform-button-link-cancel",
                                events: {
                                    click: function () {

                                        this.popupWindow.close();
                                    }
                                }
                            }),
                            new BX.PopupWindowButton({
                                text: BX.message('OTUS_BOOKINGFIELD_POPUP_BTN_OK'),
                                className: "webform-button-link-ok",
                                events: {
                                    click: function () {

                                        let formId = document.getElementById(
                                            'popup-window-content-popup-booking-procedure-' + procedure_id
                                        );

                                        let formAlertBlock = document.getElementById(
                                            'popup-alert-booking-procedure-' + procedure_id
                                        );

                                        let fio = formId.querySelector('.field-fio').value;
                                        let datetime = formId.querySelector('.field-datetime').value;

                                        BX.ajax.runAction('otus:bookingfield.BookingController.add', {
                                            data: {
                                                fields: {
                                                    fio: fio,
                                                    datetime: datetime,
                                                    procedure_id: procedure_id,
                                                    sessid: BX.bitrix_sessid()
                                                }
                                            }
                                        }).then(function (response) { // status == 'success'
                                            window.location = procedure_booking_url.replace(/#IBLOCK_ID#/g, iblock_id);
                                        }, function (response) { // status !== 'success'
                                            for (let i = 0; i < response.errors.length; i++) {
                                                let msg;

                                                if ((typeof response.errors[i].message) == 'string') {
                                                    msg = response.errors[i].message;
                                                } else if((typeof response.errors[i].message) == 'object') {
                                                    msg = response.errors[i].message[0];
                                                }

                                                formAlertBlock.innerHTML += msg + '<br/>';
                                            }
                                        });
                                    }
                                }
                            })
                        ]
                    ).show();

                });

            }
        }
    }

});