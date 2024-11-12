BX.ready(function () {

    var popup = new BX.PopupWindow("popup-message", null, {
        content: "Запсиь на процедуру",
        closeIcon: {right: "10px", top: "10px"},
        titleBar: {
            content: BX.create("span", {
                html: "Записаться на процедуру",
                'props': {'className': 'access-title-bar'}
            })
        },
        zIndex: 1000,
        offsetLeft: 0,
        offsetTop: 10,
        draggable: {restrict: true},
        closeByEsc: true, // закрытие окна по esc
        darkMode: false, // окно будет светлым или темным
        autoHide: false, // закрытие при клике вне окна
        draggable: true, // можно двигать или нет
        resizable: true, // можно ресайзить
        min_height: 500, // минимальная высота окна
        min_width: 500, // минимальная ширина окна
        lightShadow: true, // использовать светлую тень у окна
        angle: true, // появится уголок
        overlay: {
            // объект со стилями фона
            backgroundColor: 'black',
            opacity: 500
        },
        buttons: [
            new BX.PopupWindowButton({
                text: "Отмена",
                className: "webform-button-link-cancel",
                events: {
                    click: function () {

                        this.popupWindow.close();
                    }
                }
            }),
            new BX.PopupWindowButton({
                text: "Записаться",
                className: "webform-button-link-ok",
                events: {
                    click: function () {



                    }
                }
            }),
        ]
    });

    let procedures = document.querySelectorAll('.procedure-item-grid');

    if (procedures.length > 0)
    {
        for (i = 0; i < procedures.length; i++) {
            if (procedures[i].hasAttribute('data-procedure-id')) {

                let regex = /[\D]{1,}/g;
                let id = procedures[i].getAttribute('data-procedure-id').replace(regex, '');

                procedures[i].addEventListener('click', (event) => {
                    event.preventDefault();

                    console.log(id);

                    popup.show();

                });

            }
        }
    }

});

