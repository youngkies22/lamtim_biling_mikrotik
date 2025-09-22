@extends('layouts/layoutMaster')

@section('title', 'Data Pelanggan Mapping')

@section("vendor-style")
@endsection


@section("vendor-script")

@endsection

@section('page-script')
<script>
  const baseUrlRoute = "/user"; // Variabel global
  let table;
    $(document).ready(function() {
     table = $('#table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": "{{ route('user.mapping.json') }}",
        displayLength: 20,
        lengthMenu: [10, 20, 35, 40, 50, 80, 100],
        language: {
          paginate: {
            next: '<i class="ri-arrow-right-s-line"></i>',
            previous: '<i class="ri-arrow-left-s-line"></i>'
          }
        },
        columns: [
          {
            data: null,
          },

          { data: 'name'},
          {
            data: 'id',
            name: 'action',
            orderable: false,
            searchable: false,
            render: function (data) {
              console.log(data);
              return `
                <a class="dropdown-item fw-bold btn-edit" href="/user/edit/mapping/${data}">
                  <i class="mdi mdi-pencil-outline me-1"></i> Mapping
                </a>
              `;
            }
          },
          { data: 'wa'},
          { data: 'user_detail.tglDafatar'},
          { data: 'created_at'},
        ],
        order: [[4, 'desc']],
        columnDefs: [{
          className: 'control',
          //orderable: !1,
          targets: 0,
          searchable: !1,
          render: function() {
            return ''
          }
        }],

        responsive: {
          details: {
            display: $.fn.dataTable.Responsive.display.modal({
              header: function (e) {
                return "Details Data";
              }
            }),
            type: "column",
            renderer: function (api, rowIdx, columns) {
              var data = $.map(columns, function (col) {
                return col.title ? '<tr data-dt-row="' + col.rowIndex + '" data-dt-column="' + col.columnIndex + '"><td>' + col.title + ':</td> <td>' + col.data + '</td></tr>' : '';
              }).join('');
              return data ? $('<table class="table"/><tbody />').append(data) : false;
            }
          }
        }
      });


    });



</script>
@endsection

@section('content')


<!-- DataTable with Buttons -->
<div class="card">
  <div class="card-datatable table-responsive pt-0">
    <table id="table" class="datatables-basic table table-bordered">
      <thead>
        <tr>
          <th>+</th>
          <th>NAMA</th>
          <th>AKSI</th>
          <th>WA</th>
          <th>TGL DAFTAR</th>
          <th>TGL DIBUAT</th>
        </tr>
      </thead>
    </table>
  </div>
</div>

@endsection