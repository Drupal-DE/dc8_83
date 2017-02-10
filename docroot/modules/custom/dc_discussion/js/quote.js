/**
 * @file
 * Quote functions.
 */

(function ($, Drupal, drupalSettings) {

  'use strict';

  /**
   * Sets config behaviour and creates config views for quote functionality.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches quote behaviour to specific links.
   */
  Drupal.behaviors.dcDiscussionQuote = {
    attach: function (context, settings) {
      // @todo Find a safer way to access the editor.
      if (!CKEDITOR.instances.hasOwnProperty('edit-body-0-value')) {
        return;
      }
      $('.node-quote a').each(function () {
        $(this).click(function () {
          var editor = CKEDITOR.instances['edit-body-0-value'];

          // Save snapshot for undo support.
          editor.fire('saveSnapshot');

          // Extract and build text.
          var $article = $(this).closest('article');
          if (!$article) {
            // Strange things happen.
            return;
          }

          var selection = window.getSelection();

          var author = $('.uk-comment-header .username', $article).text();
          var quote = '';
          if (selection.rangeCount && !selection.isCollapsed) {
            var container = document.createElement('div');
            for (var i = 0, len = selection.rangeCount; i < len; ++i) {
              container.appendChild(selection.getRangeAt(i).cloneContents());
            }
            quote = container.innerHTML;
          }
          else {
            // Use complete post as quote.
            quote = $('.uk-comment-body .field--name-body', $article).html();
          }

          var text = '[quote=' + author + ']' + quote + '[/quote]';
          editor.insertHtml(text);

          // Save snapshot for undo support.
          editor.fire('saveSnapshot');
        });
      });
    }
  };

})(jQuery, Drupal, drupalSettings);
