import * as $ from 'jquery';
import 'datatables';

export default (function () {
  $('#dataTable').DataTable({
      "language": {
          "url": "/_secure/dt-trans/"
      }
  });
}());
