<style>
@media (max-width: 991.98px) {
  .table-mobile-cards {
    border: 0;
  }

  .table-mobile-cards thead {
    display: none;
  }

  .table-mobile-cards tbody,
  .table-mobile-cards tr,
  .table-mobile-cards td {
    display: block;
    width: 100%;
  }

  .table-mobile-cards tr {
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: .5rem;
    margin-bottom: .85rem;
    box-shadow: 0 .125rem .25rem rgba(0, 0, 0, .05);
  }

  .table-mobile-cards td {
    border: 0;
    border-bottom: 1px solid #f1f3f5;
    padding: .65rem .75rem;
    text-align: right;
    position: relative;
    min-height: 2.5rem;
  }

  .table-mobile-cards td:last-child {
    border-bottom: 0;
  }

  .table-mobile-cards td::before {
    content: attr(data-label);
    position: absolute;
    left: .75rem;
    top: .65rem;
    font-weight: 600;
    color: #495057;
    text-align: left;
    max-width: 45%;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .table-mobile-cards td[colspan] {
    text-align: center;
  }

  .table-mobile-cards td[colspan]::before {
    content: '';
  }

  .table-mobile-cards td .btn,
  .table-mobile-cards td form {
    margin: .15rem 0 .15rem .25rem;
  }
}
</style>

<script>
(function() {
  const tables = document.querySelectorAll('.table-mobile-cards');

  tables.forEach((table) => {
    const headers = Array.from(table.querySelectorAll('thead th')).map((th) => th.textContent.trim());
    if (!headers.length) {
      return;
    }

    table.querySelectorAll('tbody tr').forEach((row) => {
      const cells = row.querySelectorAll('td');
      cells.forEach((cell, index) => {
        if (cell.hasAttribute('colspan') || cell.getAttribute('data-label')) {
          return;
        }

        cell.setAttribute('data-label', headers[index] || 'Dato');
      });
    });
  });
})();
</script>
