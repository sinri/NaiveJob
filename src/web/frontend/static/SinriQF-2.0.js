/*
 * SinriQF.js
 * Version 2.0+Draft
 * Last Release 2018-12-06
 * Copyright 2018 Sinri Edogawa
 * License: GPL-3.0 with Anti 996 License
 *
 * <script src="https://unpkg.com/axios@0.18.0/dist/axios.js"></script>
 * <script src="https://unpkg.com/js-cookie@2.2.0/src/js.cookie.js"></script>
 * <script src="https://unpkg.com/vue@2.5.17/dist/vue.js"></script>
 * <script type="text/javascript" src="https://unpkg.com/iview/dist/iview.min.js"></script>
 * <link rel="stylesheet" type="text/css" href="https://unpkg.com/iview/dist/styles/iview.css">
 *
 */

let SinriQF = window.SinriQF || (function () {
    let _apiBase = "../api/";
    let _tokenName = 'token';
    let _vueInstance = null;


    let config = {
        getApiBase: function () {
            return _apiBase;
        },
        getTokenName: function () {
            return _tokenName;
        },
        getVueInstance: function () {
            return _vueInstance;
        },
        setApiBase: function (apiBase) {
            _apiBase = apiBase;
        },
        setTokenName: function (tokenName) {
            _tokenName = tokenName;
        },
        setVueInstance: function (vueInstance) {
            _vueInstance = vueInstance;
        },
    };
    let cookies = {
        getCookie: (cookieName) => {
            return Cookies.get(cookieName);
        },
        setCookie: (cookieName, cookieValue, expireTimestamp) => {
            Cookies.set(cookieName, cookieValue, {
                expires: typeof expireTimestamp === 'object' ? expireTimestamp : new Date(expireTimestamp * 1000)
            });
        },
        cleanCookie: (cookieName) => Cookies.remove(cookieName)
    };
    let api = {
        getTokenFromCookie: () => {
            return cookies.getCookie(_tokenName);
        },
        /**
         *
         * @param apiPath a string
         * @param data an object to package
         * @param callbackForData (data)=>{}
         * @param callbackForError (error,status)=>{}
         */
        call: (apiPath, data, callbackForData, callbackForError) => {
            if (!data) {
                data = {};
            }
            data.token = api.getTokenFromCookie();
            axios.post(_apiBase + apiPath, data)
                .then((response) => {
                    console.log("then", response);
                    if (response.status !== 200 || !response.data) {
                        callbackForError(response.data, response.status);
                        return;
                    }
                    let body = response.data;
                    if (body.code && body.code === 'OK') {
                        console.log("success with data", body.data);
                        callbackForData(body.data);
                        return;
                    }
                    callbackForError((body.data ? body.data : 'Unknown Error'), response.status);
                })
                .catch((error) => {
                    console.log("catch", error);
                    callbackForError(error, -1);
                })
        }
    };
    let page = {
        getParameterByName: function (name, defaultValue) {
            let match = RegExp('[?&]' + name + '=([^&]*)').exec(window.location.search);
            let v = match && decodeURIComponent(match[1].replace(/\+/g, ' '));
            return v ? v : defaultValue;
        },
        getSiteRoot: function (headPath) {
            // commonly head path is 'frontend/' against pathname "/frontend/index.html" while leading '/' is always there
            let x1 = document.location.pathname.lastIndexOf("/" + headPath);
            return document.location.protocol + "//" + document.location.host + document.location.pathname.substr(0, x1);
        }
    };
    let iview = {
        showErrorMessage: function (message, delay) {
            _vueInstance.$Message.error({
                content: message,
                duration: delay ? delay : 0,
                closable: true
            });
        },
        showInfoMessage: function (message, delay) {
            _vueInstance.$Message.info({
                content: message,
                duration: delay ? delay : 0,
                closable: true
            });
        },
        showSuccessMessage: function (message, delay) {
            _vueInstance.$Message.success({
                content: message,
                duration: delay ? delay : 0,
                closable: true
            });
        },
        showWarningMessage: function (message, delay) {
            _vueInstance.$Message.warning({
                content: message,
                duration: delay ? delay : 0,
                closable: true
            });
        },
        showNotice: function (title, desc, delay) {
            _vueInstance.$Notice.open({
                title: title,
                desc: desc,
                duration: delay ? delay : 0,
            });
        },
        showInfoNotice: function (title, desc, delay) {
            _vueInstance.$Notice.info({
                title: title,
                desc: desc,
                duration: delay ? delay : 0,
            });
        },
        showSuccessNotice: function (title, desc, delay) {
            _vueInstance.$Notice.success({
                title: title,
                desc: desc,
                duration: delay ? delay : 0,
            });
        },
        showWarningNotice: function (title, desc, delay) {
            _vueInstance.$Notice.warning({
                title: title,
                desc: desc,
                duration: delay ? delay : 0,
            });
        },
        showErrorNotice: function (title, desc, delay) {
            _vueInstance.$Notice.error({
                title: title,
                desc: desc,
                duration: delay ? delay : 0,
            });
        },
        startLoadingBar: function () {
            _vueInstance.$Loading.start();
        },
        finishLoadingBar: function () {
            _vueInstance.$Loading.finish();
        },
        finishLoadingBarWithError: function () {
            _vueInstance.$Loading.error();
        },
        updateLoadingBar: function (percent) {
            _vueInstance.$Loading.update(percent);
        },
    };
    let data = {
        copy: function (object) {
            return JSON.parse(JSON.stringify(object));
        },
        unifyJSON: function (string) {
            return JSON.stringify(JSON.parse(string));
        }
    };
    return {
        config: config,
        cookies: cookies,
        api: api,
        page: page,
        iview: iview,
        data: data,
    };
})();