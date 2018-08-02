import * as $ from 'jquery';
import 'dropify/dist/js/dropify.js';
import 'dropify/dist/css/dropify.css';

export default (function () {
    $('.dropify').dropify({
        messages: {
            default: 'Glissez-d&eacute;posez un fichier ici ou cliquez',
            replace: 'Glissez-d&eacute;posez un fichier ou cliquez pour remplacer',
            remove: 'Supprimer',
            error: 'D&eacute;sol&eacute;, le fichier est trop volumineux'
        }
    });
}())
