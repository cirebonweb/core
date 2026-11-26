/*!
 * CirebonWeb (https://cirebonweb.com)
 * Copyright (c) 2023-2026 CirebonWeb
 * Licensed under MIT (https://opensource.org/licenses/MIT)
 */

const $statContainer = $(".statContainer"),
    $tabelData = $("#tabelData");

function hapus() {
    let selected = $(".checkItem:checked"),
        rowCount = $tabelData.DataTable().data().count();
    0 !== selected.length
        ? 0 !== rowCount
            ? Swal.fire({
                title: "Konfirmasi",
                html: langText.removeTitle + "<br>" + langText.removeText,
                icon: "question",
                showCancelButton: true,
                confirmButtonColor: "#d33",
            }).then((result) => {
                if (result.isConfirmed) {
                    let ids = selected
                        .map(function () {
                            return $(this).val();
                        })
                        .get();
                    return (
                        CirebonwebForm.Fetch(Lokasi.vendor() + "user-login/hapus", { ids: ids }, "POST", {}).done(
                            function (response) {
                                if (!response.success) return;
                                CirebonwebForm.Sukses(response.messages).then(() => {
                                    $("#checkAll").prop("checked", false);
                                    let table = $tabelData.DataTable();
                                    table.ajax.reload(function () {
                                        let info = table.page.info();
                                        info.recordsDisplay > 0 &&
                                            info.end - info.start == 0 &&
                                            info.page > 0 &&
                                            table.page(info.page - 1).draw(false);
                                    }, false),
                                        $statContainer.CirebonwebStat("refresh");
                                });
                            }
                        ),
                        false
                    );
                }
            })
            : Swal.fire("Peringatan", "Data tabel kosong, proses dihentikan.", "warning")
        : Swal.fire("Peringatan", "Silakan pilih minimal satu data untuk dihapus.", "warning");
}

function reset() {
    0 !== $tabelData.DataTable().data().count()
        ? Swal.fire({
            title: "Konfirmasi",
            html: langText.removeTitle + "<br>" + langText.removeText,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#d33",
        }).then((result) => {
            if (result.isConfirmed)
                return (
                    CirebonwebForm.Fetch(Lokasi.vendor() + "user-login/reset", {}, "POST", {}).done(
                        function (response) {
                            if (!response.success) return;
                            CirebonwebForm.Sukses(response.messages).then(() => {
                                $tabelData.DataTable().ajax.reload(null, false);
                                $statContainer.CirebonwebStat("refresh");
                            });
                        }
                    ),
                    false
                );
        })
        : Swal.fire("Informasi", "Data tabel kosong, proses dihentikan.", "info");
}

$(function () {
    $statContainer.CirebonwebStat({ endpoint: Lokasi.vendor() + "statistik/user-login" }),
        $tabelData.DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: CrbMenu.url("vendor.user-login", "tabel"),
                type: "POST",
                data: function (d) { d.filter_status = $("#filter_status").val(); },
            },
            autoWidth: false,
            responsive: true,
            order: [[1, "desc"]],
            search: { return: true },
            dom: "Blfrtip",
            buttons: [
                { text: "Hapus", action: () => hapus() },
                { text: "Reset", action: () => reset() },
                {
                    text: "Reload",
                    action: function (e, dt, node, config) {
                        dt.search("").draw(),
                            $("#filter_status").val("").trigger("change"),
                            CirebonwebForm.Sukses("Selesai reload tabel ke kondisi awal").then(() => {
                                dt.ajax.reload(null, true);
                                $statContainer.CirebonwebStat("refresh");
                            });
                    },
                },
                { extend: "excelHtml5", exportOptions: { columns: ":not(.no-export)" } },
            ],
            columnDefs: [
                { targets: [0, 4, 5, 6, 7], orderable: false },
                { targets: "_all", className: "dt-head-center" },
                { targets: [0, 1, 4, 8], className: "dt-body-center" },
            ],
        }),
        $("#tabelData_filter.dataTables_filter").append($("#filter_status")),
        $(document).on("change", "#filter_status", function () {
            $tabelData.DataTable().ajax.reload(null, false);
        });
}),
    $("#checkAll").on("click", function () {
        $(".checkItem").prop("checked", this.checked);
    });
