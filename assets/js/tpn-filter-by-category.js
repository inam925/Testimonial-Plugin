jQuery(document).ready(function ($) {
  let tpn_currentPage = 1;
  let tpn_selectedCategories = [];

  $(document).on("click", "#category-button", function () {
    tpn_currentPage = 1;
    // Get all selected category IDs
    tpn_selectedCategories = $(".categories input[type='checkbox']:checked")
      .map(function () {
        return this.value;
      })
      .get();
    // filtered news
    console.log(tpn_selectedCategories);
    getNewsByPage(tpn_selectedCategories, tpn_currentPage);
  });
  $(document).on("click", "#display-all", function () {
    tpn_currentPage = 1;
    $(".categories input[type='checkbox']").prop("checked", false);
    // Get all category IDs
    tpn_selectedCategories = $(".categories input[type='checkbox']:checked")
      .map(function () {
        return this.value;
      })
      .get();
    // filtered news
    getNewsByPage(tpn_selectedCategories, tpn_currentPage);
  });

  // Attach click event handlers to the pagination links
  $(document).on("click", ".pagination-links a", function (event) {
    event.preventDefault();
    let tpn_page = parseInt($(this).text(), 10); // Get the page number from the clicked link
    // filtered news
    getNewsByPage(tpn_selectedCategories, tpn_page);
  });

  function getNewsByPage(categories, page) {
    // retrieve the filtered news

    $.ajax({
      url: filter_script_object.ajax_url,
      method: "POST",
      data: {
        action: "filter_posts",
        categories: categories,
        page: page,
      },
      success: function (response) {
        console.log(response);
        // Update the filtered news
        $(".main").html(response.data.filtered_posts);
        // Update the pagination links
        updatePagination(response.data.max_num_pages, page);
      },
      error: function (error) {
        console.log(error);
      },
    });
  }

  function updatePagination(maxNumOfPages, tpn_currentPage) {
    let tpn_paginationHTML = "";

    for (let i = 1; i <= maxNumOfPages; i++) {
      tpn_paginationHTML +=
        '<a href="#" class="' +
        (i === tpn_currentPage ? "current" : "") +
        '">' +
        i +
        "</a>";
    }
    $(".pagination-links").html(tpn_paginationHTML);
  }

  $(document).on("click", ".news-item-button", function () {
    // Get the selected post ID (value of the button)
    let tpn_postId = $(this).val();
    let tpn_expandedContent = $(".expanded-content-" + tpn_postId);

    // If the expanded content is already visible, hide it and return
    if (tpn_expandedContent.is(":visible")) {
      tpn_expandedContent.hide();
      return;
    }

    // Make an AJAX request to retrieve the filtered post
    $.ajax({
      url: filter_script_object.ajax_url,
      method: "POST",
      data: {
        action: "expand_post",
        id: tpn_postId,
      },
      success: function (response) {
        // Hide all existing expanded content
        $(".expanded-news-" + tpn_postId).html(response.content);
        tpn_expandedContent.show();
        $(".read-more-button-" + tpn_postId).hide();
      },
      error: function (error) {
        console.log(error);
      },
    });
  });

  $(document).on("click", ".news-title-button", function () {
    // Get the selected post ID (value of the button)
    let tpn_postId = $(this).val();
    let tpn_expandedContent = $(".expanded-title-" + tpn_postId);

    // If the expanded content is already visible, hide it and return
    if (tpn_expandedContent.is(":visible")) {
      tpn_expandedContent.hide();
      return;
    }

    // Make an AJAX request to retrieve the filtered post
    $.ajax({
      url: filter_script_object.ajax_url,
      method: "POST",
      data: {
        action: "expand_post",
        id: tpn_postId,
      },
      success: function (response) {
        // Hide all existing expanded content
        $(".expanded-title-" + tpn_postId).html(response.content);
        tpn_expandedContent.show();
        $(".read-more-title-button-" + tpn_postId).hide();
        $(".close-title-button-" + tpn_postId).show();
      },
      error: function (error) {
        console.log(error);
      },
    });
  });

  // Handle the close button for the expanded content
  $(document).on("click", ".close-button", function () {
    let tpn_postId = $(this).val();
    $(".read-more-button-" + tpn_postId).show();
    $(".expanded-content-" + tpn_postId).hide();
  });
  $(document).on("click", "#close-title-button", function () {
    let tpn_postId = $(this).val();
    $(".read-more-title-button-" + tpn_postId).show();
    $(".expanded-title-" + tpn_postId).hide();
    $(".close-title-button-" + tpn_postId).hide();
  });
});
