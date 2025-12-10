(function ($) {
  $(document).ready(function () {
    let tables = $(".document-library-table");
    const adminBar = $("#wpadminbar");
    const clickFilterColumns = ["doc_categories"];
    const dataTablesLanguage = data_table_params.language || {};
    if (!dataTablesLanguage.processing) {
      dataTablesLanguage.processing =
        '<div class="dots-loader">' +
        '<div class="dot"></div>' +
        '<div class="dot"></div>' +
        '<div class="dot"></div>' +
        "</div>";
    }
    tables.each(function () {
      let $table = $(this);
      this.id = $table.attr("id");
      
      // Get the table-specific params using the table ID
      const paramsVarName = 'document_library_params_' + this.id.replace(/[^a-zA-Z0-9]/g, '_');
      const document_library_params = window[paramsVarName];
      
      if (!document_library_params) {
        console.error('Document library params not found for table: ' + this.id);
        return;
      }

      // Set the column classes
      let columns = document_library_params.columns || [];
      if (typeof columns === "object" && !Array.isArray(columns)) {
        columns = Object.values(columns);
      }

      let column_classes = [];
      columns.forEach((column) => {
        column_classes.push({
          className: "col-" + column.trimStart(),
          data: column.trimStart(),
        });
      });

      // --- Determine default sort index ---
      // Get sort settings from params, defaulting to title/asc if not set
      const defaultSortColumnName = document_library_params.sort_by || "title";
      const defaultSortDirection = document_library_params.sort_order || "asc";

      let defaultSortColumnIndex = column_classes.findIndex(
        (col) => col.data === defaultSortColumnName
      );

      let config = {
        responsive: true,
        processing: true,
        serverSide: document_library_params.lazy_load,
        language: dataTablesLanguage,
      };

      // Set initial sort order
      if (document_library_params.lazy_load) {
        // Server-side mode: always set order to show correct indicators
        // Sorting is handled by server via AJAX
        if (defaultSortColumnIndex !== -1) {
          config.order = [[defaultSortColumnIndex, defaultSortDirection]];
        } else {
          config.order = [[0, "asc"]]; // fallback to first column
        }
      } else {
        // Client-side mode: data is already sorted by server
        // Only set order if column is visible, otherwise disable initial sorting
        if (defaultSortColumnIndex !== -1) {
          config.order = [[defaultSortColumnIndex, defaultSortDirection]];
        } else {
          // Sort column not visible: disable initial sorting to preserve server order
          config.order = [];
        }
      }

      // Set the ajax URL if the lazy load is enabled
      if (document_library_params.lazy_load) {
        config.ajax = {
          url: document_library_params.ajax_url,
          type: "POST",
          data: {
            table_id: document_library_params.table_id,
            action: document_library_params.ajax_action,
            category: $(".category-search-" + document_library_params.table_id).val(),
            _ajax_nonce: document_library_params.ajax_nonce,
          },
        };
      }

      config.columns = column_classes;

      // Initialize DataTable
      let table = $table.DataTable(config);

      // If scroll offset defined, animate back to top of table on next/previous page event
      $table.on("page.dt", function () {
        if ($(this).data("scroll-offset") !== false) {
          let tableOffset =
            $(this).parent().offset().top - $(this).data("scroll-offset");

          if (adminBar.length) {
            // Adjust offset for WP admin bar
            let adminBarHeight = adminBar.outerHeight();
            tableOffset -= adminBarHeight ? adminBarHeight : 32;
          }

          $("html,body").animate({ scrollTop: tableOffset }, 300);
        }
      });

      // Change the animation for the loading state
      // Listen to the processing event
      $table.on("processing.dt", function (e, settings, processing) {
        if (processing) {
          $table.find("tbody").addClass("loading"); // Show custom loader
        } else {
          $table.find("tbody").removeClass("loading"); // Hide custom loader
        }
      });

      // Add category parameter just before the AJAX request is sent
      table.on("preXhr.dt", function (e, settings, data) {
        data.category = $(".category-search-" + document_library_params.table_id).val();
      });

      // If 'search on click' enabled then add click handler for links in category, author and tags columns.
      // When clicked, the table will filter by that value.
      if ($table.data("click-filter")) {
        $table.on("click", "a", function () {
          // Don't filter the table when opening the lightbox
          if ($(this).hasClass("dlw-lightbox")) {
            return;
          }

          let $link = $(this),
            idx;
          if (table.cell($link.closest("td").get(0)).index()) {
            idx = table.cell($link.closest("td").get(0)).index().column; // get the column index
          }
          // If the element is in a child row
          if ($link.closest("td").hasClass("child")) {
            let parentLi = $link.closest("li").attr("class").split(" ")[0];
            idx = table.cell($("td." + parentLi)).index().column; // get the column index
          }
          let header = table.column(idx).header(), // get the header cell
            columnName = $(header).data("name"); // get the column name from header
          // Is the column click filterable?
          if (-1 !== clickFilterColumns.indexOf(columnName)) {
            if (!document_library_params.lazy_load) {
              table.search($link.text()).draw();
            } else {
              $(".category-search-" + document_library_params.table_id).val($link.text());
              table.draw();
            }
            return false;
          }

          return true;
        });
      }
    }); // each table

    /**
     * Open Lightbox
     */
    $(document).on(
      "click",
      ".document-library-table a.dlw-lightbox",
      function (event) {
        event.preventDefault();
        event.stopPropagation();

        const pswpElement = $(".pswp")[0];
        const $img = $(this).find("img");

        if ($img.length < 1) {
          return;
        }

        const items = [
          {
            src: $img.attr("data-large_image"),
            w: $img.attr("data-large_image_width"),
            h: $img.attr("data-large_image_height"),
            title:
              $img.attr("data-caption") && $img.attr("data-caption").length
                ? $img.attr("data-caption")
                : $img.attr("title"),
          },
        ];

        const options = {
          index: 0,
          shareEl: false,
          closeOnScroll: false,
          history: false,
          hideAnimationDuration: 0,
          showAnimationDuration: 0,
        };

        // Initializes and opens PhotoSwipe
        let photoswipe = new PhotoSwipe(
          pswpElement,
          PhotoSwipeUI_Default,
          items,
          options
        );
        photoswipe.init();

        return false;
      }
    );
  }); // end document.ready
})(jQuery);
