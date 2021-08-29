import 'es6-promise/auto';
import Swal from 'sweetalert2';

declare global {
  interface Window {
    google_ss2db_data: GoogleSS2dbData;
  }
}
declare let jQuery: any;

interface GoogleSS2dbData {
  nonce: string;
  plugin_dir_url: string;
}

interface GoogleSS2dbResponse {
  id: string;
}

export const deleteData = (): void => {
  // const beforeSend = (): Promise<string> =>
  //   new Promise<string>(resolve => {

  //     resolve('resolved');
  //   });

  const asyncPreConfirm = async (theid: string): Promise<GoogleSS2dbResponse> => {
    // await beforeSend();

    return fetch(`${window.google_ss2db_data.plugin_dir_url}includes/delete.php`, {
      method: 'POST',
      credentials: 'same-origin',
      headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Content-Type': 'application/x-www-form-urlencoded; charset=utf-8',
        'Cache-Control': 'no-cache',
      },
      body: `id=${theid}&nonce=${window.google_ss2db_data.nonce}`,
    })
      .then(response => {
        if (!response.ok) {
          throw new Error(response.statusText);
        }
        return response.json();
      })
      .catch(error => {
        Swal.showValidationMessage(`Request failed: ${error}`);
        console.log(error);
      });
  };

  jQuery('.acorddion .ss2db_delete').on('click', (e: { currentTarget: HTMLElement }) => {
    const theid: string = jQuery(e.currentTarget).closest('dl').attr('data-id');
    Swal.fire({
      title: 'Are you sure?',
      text: "You won't be able to revert this!",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#3085d6',
      cancelButtonColor: '#d33',
      confirmButtonText: 'OK!',
      showLoaderOnConfirm: true,
      allowOutsideClick: false,
      preConfirm: () => {
        return asyncPreConfirm(theid);
      },
    }).then(result => {
      // console.log(result);
      if (result.isConfirmed) {
        if (result.value) {
          const ele: JQuery = jQuery(`.acorddion[data-id='${result.value?.id}']`);
          jQuery
            .when(
              ele.stop(true, true).animate(
                {
                  height: '0',
                  margin: '0',
                },
                300,
              ),
            )
            .done(() => {
              setTimeout(() => {
                ele.remove();
              }, 600);
            });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Something went wrong!',
          });
        }
      }
    });

    return false;
  });
};
