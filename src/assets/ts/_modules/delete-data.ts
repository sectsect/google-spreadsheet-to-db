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

export const deleteData = () => {
  jQuery('.acorddion .ss2db_delete').on('click', (e: { currentTarget: HTMLElement }) => {
    const theid = jQuery(e.currentTarget)
      .closest('dl')
      .attr('data-id');
    Swal.queue([
      {
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'OK!',
        showLoaderOnConfirm: true,
        allowOutsideClick: false,
        preConfirm() {
          return new Promise(resolve => {
            jQuery
              .ajax({
                url: `${window.google_ss2db_data.plugin_dir_url}includes/delete.php`,
                dataType: 'json',
                data: { id: theid, nonce: window.google_ss2db_data.nonce },
                type: 'post',
                // beforeSend() {},
              })
              .done(() => {
                Swal.insertQueueStep({
                  icon: 'success',
                  title: 'Deleted!',
                });
              })
              .fail(() => {
                Swal.insertQueueStep({
                  icon: 'error',
                  title: 'Something went wrong!',
                });
              })
              .always((data: { res: number; id: string }) => {
                if (data.res === 1) {
                  const ele = jQuery(`.acorddion[data-id='${data.id}']`);
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
                }
                resolve();
              });
          });
        },
      },
    ]);
  });
};
