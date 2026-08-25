// Each icon is a list of SVG child elements (tag + attrs) rendered inside a
// 24x24 viewBox by Icon.vue. Stroke, fill, and linecap are all set once on
// the <svg> root there — never repeat them per-icon here.
//
// To add a new icon: drop in a new key, then use it as <Icon name="thatKey" />.

export default {
    home: [
        { tag: 'path', attrs: { d: 'M4 10.5 12 4l8 6.5' } },
        { tag: 'path', attrs: { d: 'M6 9.5V19a1 1 0 0 0 1 1h3v-5h4v5h3a1 1 0 0 0 1-1V9.5' } },
    ],

    users: [
        { tag: 'circle', attrs: { cx: '9', cy: '8', r: '3' } },
        { tag: 'path', attrs: { d: 'M2.5 20a6.5 6.5 0 0 1 13 0' } },
        { tag: 'path', attrs: { d: 'M16 4.5a3 3 0 0 1 0 6' } },
        { tag: 'path', attrs: { d: 'M17.5 14a6.5 6.5 0 0 1 4 6' } },
    ],

    bookOpen: [
        { tag: 'path', attrs: { d: 'M3 5.5c2-1 5-1.2 9 .5v13c-4-1.7-7-1.5-9-.5v-13Z' } },
        { tag: 'path', attrs: { d: 'M21 5.5c-2-1-5-1.2-9 .5v13c4-1.7 7-1.5 9-.5v-13Z' } },
    ],

    award: [
        { tag: 'circle', attrs: { cx: '12', cy: '8', r: '5' } },
        { tag: 'path', attrs: { d: 'M9 12.5 7.5 21l4.5-2.5L16.5 21 15 12.5' } },
    ],

    plus: [
        { tag: 'path', attrs: { d: 'M12 5v14M5 12h14' } },
    ],

    close: [
        { tag: 'path', attrs: { d: 'M6 6l12 12M18 6L6 18' } },
    ],

    edit: [
        { tag: 'path', attrs: { d: 'M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7' } },
        { tag: 'path', attrs: { d: 'M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z' } },
    ],

    trash: [
        { tag: 'path', attrs: { d: 'M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2m3 0v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6h14Z' } },
    ],

    printer: [
        { tag: 'path', attrs: { d: 'M6 9V2h12v7' } },
        { tag: 'path', attrs: { d: 'M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2' } },
        { tag: 'path', attrs: { d: 'M6 14h12v8H6z' } },
    ],

    chevronDown: [
        { tag: 'path', attrs: { d: 'M6 9l6 6 6-6' } },
    ],

    logOut: [
        { tag: 'path', attrs: { d: 'M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4' } },
        { tag: 'path', attrs: { d: 'M16 17l5-5-5-5' } },
        { tag: 'path', attrs: { d: 'M21 12H9' } },
    ],
    referral: [
        { tag: 'circle', attrs: { cx: '6', cy: '12', r: '2.5' } },
        { tag: 'circle', attrs: { cx: '18', cy: '6', r: '2.5' } },
        { tag: 'circle', attrs: { cx: '18', cy: '18', r: '2.5' } },
        { tag: 'path', attrs: { d: 'M8.2 10.8 15.8 7.2M8.2 13.2l7.6 3.6' } },
    ],

    settings: [
        { tag: 'circle', attrs: { cx: '12', cy: '12', r: '3' } },
        { tag: 'path', attrs: { d: 'M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.6 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.6a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z' } },
    ],
    verify: [
        { tag: 'circle', attrs: { cx: '12', cy: '12', r: '9' } },
        { tag: 'path', attrs: { d: 'M8 12.5l2.5 2.5L16 9' } },
    ],
    verifyShield: [
        { tag: 'path', attrs: { d: 'M12 3l7 3v6c0 4.5-3 8-7 9-4-1-7-4.5-7-9V6l7-3Z' } },
        { tag: 'path', attrs: { d: 'M8.5 12.5l2.5 2.5 4.5-5' } },
    ],
    subscription: [
        {
            tag: 'rect',
            attrs: {
                x: '3',
                y: '5',
                width: '18',
                height: '14',
                rx: '2',
            },
        },
        {
            tag: 'path',
            attrs: {
                d: 'M3 10h18',
            },
        },
        {
            tag: 'path',
            attrs: {
                d: 'M7 15h3',
            },
        },
    ],
    info: [
        { tag: 'circle', attrs: { cx: '12', cy: '12', r: '9' } },
        { tag: 'path', attrs: { d: 'M12 11v5' } },
        { tag: 'path', attrs: { d: 'M12 8h.01' } },
    ],
    wallet: [
        { tag: 'path', attrs: { d: 'M19 7V4a1 1 0 0 0-1-1H5a2 2 0 0 0 0 4h15a1 1 0 0 1 1 1v4' } },
        { tag: 'path', attrs: { d: 'M3 5v14a2 2 0 0 0 2 2h15a1 1 0 0 0 1-1v-4' } },
        { tag: 'path', attrs: { d: 'M18 12a2 2 0 0 0 0 4h4v-4z' } },
    ],
};
