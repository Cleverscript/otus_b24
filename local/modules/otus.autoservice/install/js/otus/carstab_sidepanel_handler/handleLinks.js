BX.SidePanel.Instance.bindAnchors({
    rules: [
        {
            condition: [
                "/car/(\\d+)/",
            ],
            options: {
                requestMethod: "post",
                requestParams: {},
                cacheable: false,
                events: {},
                width: 800,
            },
        },
    ]
});
