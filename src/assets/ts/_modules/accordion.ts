declare let jQuery: any;

export const accordion = (): void => {
  jQuery('dl.acorddion dt span')
    .not('.ss2db_delete')
    .on('click', (e: { currentTarget: HTMLElement }) => {
      jQuery(e.currentTarget).parent().next().slideToggle();
      jQuery(e.currentTarget).closest('dl').toggleClass('opened');
    });
};
