/**
 * Recursively convert an array or object to an HTML table
 *
 * @param arr - Array or object to be converted
 * @returns HTML table representation of the array/object
 */
export class RecursiveTable {
  /**
   * Convert JSON text to HTML debug output
   *
   * @param jsonText - JSON text to be converted
   * @returns HTML representation of the JSON
   */
  public static jsonToDebug(jsonText: string = ''): string {
    try {
      const arr = JSON.parse(jsonText);
      return this.arrayToHtmlTableRecursive(arr);
    } catch (error) {
      console.error('JSON parsing error:', error);
      return '';
    }
  }

  /**
   * Recursively convert an array or object to an HTML table
   *
   * @param arr - Array or object to be converted
   * @returns HTML table representation of the array/object
   */
  private static arrayToHtmlTableRecursive(arr: any): string {
    if (!arr || typeof arr !== 'object') {
      return '';
    }

    let str = '<table class="entry-details-table"><tbody>';

    const keys = Object.keys(arr);
    for (let i = 0; i < keys.length; i += 1) {
      const key = keys[i];
      const val = arr[key];

      str += '<tr>';
      str += `<th><span>${this.escapeHtml(String(key))}</span></th>`;
      str += '<td>';

      if (Array.isArray(val) || (val !== null && typeof val === 'object')) {
        if (Object.keys(val).length > 0) {
          str += this.arrayToHtmlTableRecursive(val);
        }
      } else {
        str += `<span>${this.escapeHtml(String(val)).replace(/\n/g, '<br>')}</span>`;
      }

      str += '</td></tr>';
    }

    str += '</tbody></table>';
    return str;
  }

  /**
   * Escape HTML special characters
   *
   * @param unsafe - String to be escaped
   * @returns Escaped HTML string
   */
  private static escapeHtml(unsafe: string): string {
    return unsafe
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }
}
