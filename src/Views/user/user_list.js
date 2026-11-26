/*!
 * CirebonWeb (https://cirebonweb.com)
 * Copyright (c) 2023-2026 CirebonWeb
 * Licensed under MIT (https://opensource.org/licenses/MIT)
 */

// Fungsi berikut dipanggil dari inline onclick/onchange pada HTML
// (tombol edit, dropdown status, ikon lihat password) yang dievaluasi
// di global scope, sehingga wajib diekspos ke window karena file ini
// dibungkus document.addEventListener('DOMContentLoaded', ...).
window.simpan = simpan;
window.cekpwd = cekpwd;
window.updateStatus = updateStatus;

const $statContainer = $(".statContainer"),
    $tabelData = $("#tabelData"),
    $formData = $("#formData"),
    $modalDiv = $("#modal-div"),
    formCek = CirebonwebForm.Cek($formData);

let currentPostUrl = "";
let defaultCanManageGroup = !$formData.find("#group").prop("disabled");
let defaultGroupOptions = {};
$formData.find("#group option").each(function () {
    if (this.value) {
        defaultGroupOptions[this.value] = $(this).text();
    }
});

function normalisasiGrupTerpilih(selectedGroup) {
    if (Array.isArray(selectedGroup)) {
        return selectedGroup.filter(function (group) { return typeof group === "string" && group !== ""; });
    }
    if (typeof selectedGroup === "string" && selectedGroup !== "") {
        return selectedGroup.split(",").map(function (group) { return group.trim(); }).filter(Boolean);
    }
    return [];
}

function normalisasiOpsiGrup(groups) {
    if (Array.isArray(groups)) {
        const options = {};
        groups.forEach(function (group) {
            if (typeof group === "string" && group !== "") {
                options[group] = group;
            } else if (group && typeof group === "object" && typeof group.key === "string") {
                options[group.key] = typeof group.title === "string" ? group.title : group.key;
            }
        });
        return options;
    }

    if (groups && typeof groups === "object") {
        const options = {};
        Object.keys(groups).forEach(function (key) {
            const value = groups[key];
            if (typeof value === "string") {
                options[key] = value;
            } else if (value && typeof value === "object" && typeof value.title === "string") {
                options[key] = value.title;
            }
        });
        return options;
    }

    return {};
}

function initSelectGrup() {
    const $group = $formData.find("#group");
    if ($.fn.select2 && !$group.hasClass("select2-hidden-accessible")) {
        $group.select2({
            dropdownParent: $modalDiv,
            width: "100%",
            // placeholder: $group.data("placeholder") || "Pilih hak akses pengguna",
            allowClear: true
        });
    }
}

function isiPilihanGrup(groups, selectedGroup) {
    const $group = $formData.find("#group");
    const options = normalisasiOpsiGrup(groups);
    const keys = Object.keys(options);
    const selected = normalisasiGrupTerpilih(selectedGroup);

    $group.prop("multiple", true).attr("name", "group[]");
    $group.empty();
    keys.forEach(function (key) {
        $group.append($('<option>', { value: key, text: options[key] }));
    });
    $group.val(selected);
    if ($.fn.select2 && $group.hasClass("select2-hidden-accessible")) {
        $group.trigger("change.select2");
    } else {
        $group.trigger("change");
    }
}

function aturFieldGrup(canEdit, selectedGroup, groups) {
    const $group = $formData.find("#group");
    const $help = $formData.find("#groupHelp");
    let options = normalisasiOpsiGrup(groups);
    const selected = normalisasiGrupTerpilih(selectedGroup);

    if (Object.keys(options).length === 0) {
        selected.forEach(function (key) { options[key] = key; });
    }

    initSelectGrup();
    isiPilihanGrup(options, selectedGroup);
    $group.prop("disabled", !canEdit);
    if ($.fn.select2 && $group.hasClass("select2-hidden-accessible")) {
        $group.trigger("change.select2");
    }
    $help.text(canEdit ? "Pilih satu atau lebih hak akses pengguna." : "Hak akses tidak dapat diubah dengan akun ini.");
}

let validatorSiap = false;

// Form Submit (Insert/Update)
function simpan(id) {
    let $modalTitle = $modalDiv.find(".modal-title"),
        $btnSubmit = $modalDiv.find("#btnSubmit");

    // Tentukan URL POST: Simpan atau Update
    if (id > 0) {
        CirebonwebForm.Fetch(CrbMenu.url("vendor.user-list", "get-id"), { id }, "POST", {}).done(function (response) {
            if (!response.success) return;
            const data = response.data;

            $modalTitle.text("Edit Data");
            $btnSubmit.text("Update");
            currentPostUrl = CrbMenu.url("vendor.user-list", "update");

            $formData.find("#id").val(data.id);
            $formData.find("#username").val(data.username);
            $formData.find("#email").val(data.secret);
            $formData.find("#password, #password_confirm").val("");
            aturFieldGrup(!!data.can_edit_group, data.groups || data.group || "", data.group_options || []);
            $modalDiv.modal("show");
        });
    } else {
        $modalTitle.text("Tambah Data");
        $btnSubmit.text("Simpan");
        currentPostUrl = CrbMenu.url("vendor.user-list", "simpan");
        $formData.find("#id").val("");
        $formData.find("#password, #password_confirm").val("");
        aturFieldGrup(defaultCanManageGroup, [], defaultGroupOptions);
        $modalDiv.modal("show");
    }

    if (validatorSiap) return;

    // Form Validasi
    $formData.validate($.extend(CirebonwebForm.Validasi(), {
        rules: {
            username: { required: true, minlength: 3, maxlength: 30 },
            email: { required: true, customEmail: true },
            password: {
                // Wajib diisi hanya saat mode Simpan (#id kosong/0)
                required: function () {
                    let valId = $("#id").val();
                    return valId === "" || valId === "0" || valId === null;
                },
                minlength: 8
            },
            password_confirm: {
                // Wajib diisi jika field password terisi
                required: function () {
                    return $("#password").val().length > 0;
                },
                equalTo: "#password"
            }
        },
        submitHandler: function () {
            if (typeof formCek !== 'undefined' && !formCek.isChanged()) return false;

            CirebonwebForm.Fetch(currentPostUrl, $formData.serializeArray(), "POST", {
                before_call: function () {
                    $btnSubmit.attr("disabled", true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Loading...');
                },
                after_call: function () {
                    $btnSubmit.attr("disabled", false).html("Submit");
                }
            })
                .done(function (response) {
                    if (!response.success) return;
                    $modalDiv.modal("hide");
                    CirebonwebForm.Sukses(response.messages).then(() => {
                        $tabelData.DataTable().ajax.reload(null, false);
                        $statContainer.CirebonwebStat("refresh");
                    });
                });
            return false;
        }
    }));
    validatorSiap = true;
}

function cekpwd() {
    "password" === $(".passwd").get(0).type
        ? ($(".ikon").html('<i class="bi bi-eye-slash-fill"></i>'),
            $("#password, #password_confirm").each(function () {
                this.type = "text";
            }))
        : ($(".ikon").html('<i class="bi bi-eye-fill"></i>'),
            $("#password, #password_confirm").each(function () {
                this.type = "password";
            }));
}

function updateStatus(select, iduser) {
    const active = select.value;
    CirebonwebForm.Fetch(CrbMenu.url("vendor.user-list", "update-status"), { iduser: iduser, active: active }, "POST", {})
        .done(function (res) {
            res.success
                ? CirebonwebForm.Sukses(res.messages).then(function () { $statContainer.CirebonwebStat("refresh") })
                : $tabelData.DataTable().ajax.reload(null, false);
        })
        .fail(function () {
            $tabelData.DataTable().ajax.reload(null, false);
        });
}

$(function () {
    $statContainer.CirebonwebStat({ endpoint: Lokasi.vendor() + "statistik/user-list" }),
        $tabelData.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: CrbMenu.url("vendor.user-list", "tabel"),
                type: "POST",
                data: function (d) { (d.filter_active = $("#filter_active").val()) },
            },
            autoWidth: false,
            responsive: true,
            language: { lengthMenu: "_MENU_" },
            search: { return: true },
            dom: "Blfrtip",
            buttons: [
                { text: "Add", action: () => simpan() },
                { extend: "excelHtml5", exportOptions: { columns: ":not(.no-export)" } },
                {
                    text: "Reload",
                    action: function (e, dt, node, config) {
                        dt.search("").draw(),
                            CirebonwebForm.Sukses("Selesai reload tabel ke kondisi awal").then(() => {
                                $("#filter_active").val("").trigger("change");
                                dt.ajax.reload(null, true);
                                $statContainer.CirebonwebStat("refresh")
                            });
                    }
                }
            ],
            columnDefs: [
                { targets: "_all", className: "dt-head-center" },
                { targets: [0, 3, 5, 8], className: "dt-body-center" },
                { targets: [5, 6, 7], render: function (data, type, row) { return CirebonwebFormat.Tanggal(data) } },
                { targets: [3, 8], orderable: false },
                { targets: [9], visible: false },
            ],
        });

    CirebonwebTable.Filter($('#filter_active'));
    $(document).on('change', '#filter_active', () => CirebonwebTable.Reload());

    $modalDiv
        .on('shown.bs.modal', function () { formCek.resetInitial(); })
        .on('hidden.bs.modal', function () {
            CirebonwebForm.Reset(this);
            $formData.find("#id").val("");
            aturFieldGrup(defaultCanManageGroup, [], defaultGroupOptions);
        });
})
