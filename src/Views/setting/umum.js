/*!
 * CirebonWeb (https://cirebonweb.com)
 * Copyright (c) 2023-2026 CirebonWeb
 * Licensed under MIT (https://opensource.org/licenses/MIT)
 */

let $sistemForm = $("#sistemForm"),
    $sistemSubmit = $("#sistemSubmit"),
    $situsForm = $("#situsForm"),
    $situsSubmit = $("#situsSubmit"),
    $smtpForm = $("#smtpForm"),
    $smtpSubmit = $("#smtpSubmit");

function cekpwd() {
    let input = $("#smtpPass").get(0);
    "password" === input.type
        ? ($(".ikon").html("<i class='bi bi-eye-slash'></i>"), (input.type = "text"))
        : ($(".ikon").html("<i class='bi bi-eye'></i>"), (input.type = "password"));
}

window.cekpwd = cekpwd;

$(function () {
    $('[data-toggle="tooltip"]').tooltip(),
        $("#smtpCrypto").on("change", function () {
            let port = "ssl" === $(this).val() ? 465 : 25;
            $("#smtpPort").val(port);
        });
});

$(function () {
    let formCek = CirebonwebForm.Cek($sistemForm);
    $sistemForm.validate(
        $.extend(CirebonwebForm.Validasi(), {
            ignore: [],
            rules: {
                authRegistration: { required: !0 },
                authMagicLink: { required: !0 },
                authRemembering: { required: !0 },
                authRememberLength: { required: !0, digits: !0, minlength: 5, maxlength: 30 },
            },
            submitHandler: function () {
                return (
                    !!formCek.isChanged() &&
                    (CirebonwebForm.Fetch(
                        Lokasi.vendor() + "setting-umum/simpan-sistem",
                        $sistemForm.serializeArray(),
                        "POST",
                        {
                            before_call: function () {
                                if (!formCek.isChanged()) return !1;
                                $sistemSubmit
                                    .attr("disabled", !0)
                                    .html(
                                        '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Loading...'
                                    );
                            },
                            after_call: function () {
                                $sistemSubmit.attr("disabled", !1).html("Simpan");
                            },
                        }
                    ).done(function (response) {
                        if (!response.success) return;
                        CirebonwebForm.Sukses(response.messages).then(() => {
                            formCek.resetInitial();
                            CirebonwebForm.Reset($situsForm);
                        });
                    }),
                        !1)
                );
            },
        })
    );
});

$(function () {
    let formCek = CirebonwebForm.Cek($situsForm);
    $situsForm.validate(
        $.extend(CirebonwebForm.Validasi(), {
            ignore: [],
            rules: {
                siteNama: { required: !0, minlength: 5, maxlength: 50 },
                siteTagline: { required: !0, minlength: 5, maxlength: 50 },
                siteTelp: { required: !1, digits: !0, minlength: 5, maxlength: 50 },
                siteWa: { required: !0, digits: !0, minlength: 8, maxlength: 15 },
                siteTelegram: { required: !1, digits: !0, minlength: 8, maxlength: 15 },
                siteEmail: { required: !0, customEmail: !0 },
                siteAlamat: { required: !0, minlength: 10, maxlength: 255 },
            },
            submitHandler: function () {
                return (
                    !!formCek.isChanged() &&
                    (CirebonwebForm.Fetch(
                        Lokasi.vendor() + "setting-umum/simpan-situs",
                        $situsForm.serializeArray(),
                        "POST",
                        {
                            before_call: function () {
                                if (!formCek.isChanged()) return !1;
                                $situsSubmit
                                    .attr("disabled", !0)
                                    .html(
                                        '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Loading...'
                                    );
                            },
                            after_call: function () {
                                $situsSubmit.attr("disabled", !1).html("Simpan");
                            },
                        }
                    ).done(function (response) {
                        if (!response.success) return;
                        CirebonwebForm.Sukses(response.messages).then(() => {
                            formCek.resetInitial();
                            CirebonwebForm.Reset($situsForm);
                        });
                    }),
                        !1)
                );
            },
        })
    );
});

$(function () {
    let formCek = CirebonwebForm.Cek($smtpForm);
    $smtpForm.validate(
        $.extend(CirebonwebForm.Validasi(), {
            ignore: [],
            rules: {
                smtpEmail: { required: !0, minlength: 10, maxlength: 100, customEmail: !0 },
                smtpNama: { required: !0, minlength: 5, maxlength: 50 },
                smtpPenerima: { required: !0, minlength: 10, maxlength: 100, customEmail: !0 },
                smtpHost: { required: !0, minlength: 10, maxlength: 100 },
                smtpProtocol: { required: !0 },
                smtpCrypto: { required: !1 },
                smtpPort: { required: !0, digits: !0, minlength: 2, maxlength: 5 },
                smtpUser: { required: !0, minlength: 10, maxlength: 100, customEmail: !0 },
                smtpPass: { required: !1, minlength: 8 },
            },
            submitHandler: function () {
                return (
                    !!formCek.isChanged() &&
                    (CirebonwebForm.Fetch(
                        Lokasi.vendor() + "setting-umum/simpan-smtp",
                        $smtpForm.serializeArray(),
                        "POST",
                        {
                            before_call: function () {
                                if (!formCek.isChanged()) return !1;
                                $smtpSubmit
                                    .attr("disabled", !0)
                                    .html(
                                        '<span class="spinner-border spinner-border-sm mr-1" role="status" aria-hidden="true"></span> Loading...'
                                    );
                            },
                            after_call: function () {
                                $smtpSubmit.attr("disabled", !1).html("Simpan");
                            },
                        }
                    ).done(function (response) {
                        if (!response.success) return;
                        CirebonwebForm.Sukses(response.messages).then(() => {
                            formCek.resetInitial();
                            CirebonwebForm.Reset($situsForm);
                        });
                    }),
                        !1)
                );
            },
        })
    );
});