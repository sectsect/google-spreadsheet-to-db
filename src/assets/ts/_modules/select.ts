import TomSelect from 'tom-select';

export const select = (): void => {
  const settings = {
    controlInput: undefined,
    // allowEmptyOption: true,
    // hideSelected: null,
  };

  // eslint-disable-next-line no-new
  new TomSelect('#google_ss2db_dataformat', settings);
};
