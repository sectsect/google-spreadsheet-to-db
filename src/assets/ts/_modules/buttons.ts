import Swal from 'sweetalert2';
import { RecursiveTable } from './utils';

declare global {
  interface Window {
    google_ss2db_data: GoogleSS2dbData;
  }
}

interface GoogleSS2dbData {
  nonce: string;
  plugin_dir_url: string;
  ajax_url: string;
}

/**
 * Deletes a spreadsheet entry via an AJAX request
 *
 * @param id - The unique identifier of the entry to delete
 * @param row - The table row element to be removed from the DOM
 */
const deleteSpreadsheetEntry = (
  id: string | undefined,
  row: HTMLElement | null,
) => {
  if (!id) return;

  const formData = new FormData();
  formData.append('action', 'delete_spreadsheet_entry');
  formData.append('id', id);
  formData.append('nonce', window.google_ss2db_data.nonce);

  fetch(window.google_ss2db_data.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        if (row) row.remove();
        Swal.fire('Deleted', 'The entry has been deleted', 'success');
      } else {
        Swal.fire('Error', data.data, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire('Error', 'An unexpected error occurred', 'error');
    });
};

/**
 * Initializes delete functionality for entries by adding click event listeners
 * to delete buttons that trigger a confirmation dialog
 */
export const initDeleteData = () => {
  const deleteButtons = document.querySelectorAll('.delete-entry');

  deleteButtons.forEach(button => {
    button.addEventListener('click', event => {
      const target = event.currentTarget as HTMLButtonElement;
      const { id } = target.dataset;
      const row = target.closest('tr');

      Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Delete',
      }).then(result => {
        if (result.isConfirmed) {
          deleteSpreadsheetEntry(id, row);
        }
      });
    });
  });
};

/**
 * Fetches details of a specific spreadsheet entry and displays them using SweetAlert
 *
 * @param id - The unique identifier of the entry to retrieve
 */
const fetchSpreadsheetEntryDetails = (id?: string) => {
  if (!id) return;

  const formData = new FormData();
  formData.append('action', 'get_spreadsheet_entry_details');
  formData.append('id', id);
  formData.append('nonce', window.google_ss2db_data.nonce);

  fetch(window.google_ss2db_data.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          title: `Details (ID: ${id})`,
          html: RecursiveTable.jsonToDebug(JSON.stringify(data.data.array)),
          width: '80%',
          padding: '1rem',
          animation: false,
        });
      } else {
        Swal.fire('Error', data.data, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire('Error', 'An unexpected error occurred', 'error');
    });
};

/**
 * Initializes view details functionality by adding click event listeners
 * to buttons that fetch and display entry information
 */
export const initViewDetails = () => {
  const detailButtons = document.querySelectorAll('.view-details');

  detailButtons.forEach(button => {
    button.addEventListener('click', event => {
      const target = event.currentTarget as HTMLButtonElement;
      const { id } = target.dataset;

      fetchSpreadsheetEntryDetails(id);
    });
  });
};

/**
 * Fetches raw details of a specific spreadsheet entry and displays them using SweetAlert
 *
 * @param id - The unique identifier of the entry to retrieve
 */
const fetchSpreadsheetEntryRawDetails = (id?: string) => {
  if (!id) return;

  const formData = new FormData();
  formData.append('action', 'get_spreadsheet_entry_details');
  formData.append('id', id);
  formData.append('nonce', window.google_ss2db_data.nonce);

  fetch(window.google_ss2db_data.ajax_url, {
    method: 'POST',
    body: formData,
  })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        Swal.fire({
          title: `Raw Data (ID: ${id})`,
          html: `<pre>${data.data.raw}</pre>`,
          width: '80%',
          padding: '1rem',
          animation: false,
        });
      } else {
        Swal.fire('Error', data.data, 'error');
      }
    })
    .catch(error => {
      console.error('Error:', error);
      Swal.fire('Error', 'An unexpected error occurred', 'error');
    });
};

/**
 * Initializes view raw details functionality by adding click event listeners
 * to buttons that fetch and display raw entry information
 */
export const initViewRawDetails = () => {
  const rawDataButtons = document.querySelectorAll('.view-raw-data');

  rawDataButtons.forEach(button => {
    button.addEventListener('click', event => {
      const target = event.currentTarget as HTMLButtonElement;
      const { id } = target.dataset;

      fetchSpreadsheetEntryRawDetails(id);
    });
  });
};
