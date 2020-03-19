export const accordion = () => {
  jQuery('dl.acorddion dt span')
    .not('.ss2db_delete')
    .on('click', function() {
      jQuery(this)
        .parent()
        .next()
        .slideToggle();
      jQuery(this)
        .closest('dl')
        .toggleClass('opened');
    });
};
