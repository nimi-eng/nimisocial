//sidebar
const menuItems = document.querySelectorAll('.menu-item');

// messages
const messagesNotification = document.querySelector('#messages-notifications');
const messages = document.querySelector('.messages');
const message = messages.querySelectorAll('message');
const messageSearch = document.querySelector('#message-search');

//theme 
const theme = document.querySelector('#theme');
const themeModal = document.querySelector('.customize-theme');
const fontSizes = document.querySelectorAll('.choose-size span');
var root = document.querySelector(':root');
const colorPalette = document.querySelectorAll('.choose-color span');
const Bg1 = document.querySelector('.bg-1');
const Bg2 = document.querySelector('.bg-2');
const Bg3 = document.querySelector('.bg-3');
// remove active class from all menu items
const changeActiveItem = () => {
    menuItems.forEach(item => {
        item.classList.remove('active');
    })
}
menuItems.forEach(item => {
    item.addEventListener('click', () =>{
        changeActiveItem();
        item.classList.add('active');
        if(item.id != 'notifications'){
            document.querySelector('.notifications-popup').
            style.display = 'none';
        } else{
            document.querySelector('.notifications-popup').
            style.display = 'block';
            document.querySelector('#notifications .notification-count').style.display = 'none';
        }
        if (item.id === 'addfriend') {
            document.querySelector('.addfriend').style.display = 'block';
        } else {
            document.querySelector('.addfriend').style.display = 'none';
        }
    })
}) 

//===========messages======

// search chat
const searchMessage = () => {
    const val = messageSearch.value.toLowerCase();
    message.forEach(user => {
        let name = user.querySelectorAll('h5').textContent.toLowerCase();
        if(name.indexOf(val) != -1){
            user.style.display = 'flex';
        }else{
            user.style.display = 'none';
        }
    })
}

//search chat
messageSearch.addEventListener('keyup', searchMessage);

messagesNotification.addEventListener('click', () => {
    messages.style.boxShadow = '0 0 1rem var(--color-primary)';
    messagesNotification.querySelector('.notification-count').style.display = 'none';
    setTimeout(() => {
        messages.style.boxShadow = 'none';
    }, 2000);
})

// theme/display customization
//opens modal
const openThemeModal = () => {
    themeModal.style.display = 'grid';
}

//close modal
const closeThemeModal = (e) => {
    if(e.target.classList.contains('customize-theme')){
    themeModal.style.display = 'none';
    }
}
// closing the modal
themeModal.addEventListener('click', closeThemeModal);
theme.addEventListener('click', openThemeModal);

// =======================fonts ===========
//remove active class from spans or font size selectors
const removeSizeSelector = () => {
    fontSizes.forEach(size => {
        size.classList.remove('active');
    })
}

fontSizes.forEach(size => {
    size.addEventListener('click', () =>{
        removeSizeSelector();
        let fontSize;
        size.classList.toggle('active');

        if(size.classList.contains('font-size-1')){
            fontSize = '10px';
            root.style.setProperty('--sticky-top-left', '5.4rem');
            root.style.setProperty('--sticky-top-right', '5.4rem');
        } else if (size.classList.contains('font-size-2')){
            fontSize = '13px';
            root.style.setProperty('--sticky-top-left', '5.4rem');
            root.style.setProperty('--sticky-top-right', '-7rem');
        }else if (size.classList.contains('font-size-3')){
            fontSize = '16px';
            root.style.setProperty('--sticky-top-left', '-2rem');
            root.style.setProperty('--sticky-top-right', '-17rem');
        }else if (size.classList.contains('font-size-4')){
            fontSize = '19px';
            root.style.setProperty('--sticky-top-left', '-5rem');
            root.style.setProperty('--sticky-top-right', '-25rem');
        }else if (size.classList.contains('font-size-5')){
            fontSize = '22px';
            root.style.setProperty('--sticky-top-left', '-12rem');
            root.style.setProperty('--sticky-top-right', '-35rem');
        }
                //change font size of the root html element
                document.querySelector('html').style.fontSize = fontSize;
    })
               
})

// remove active class from colors
const changeActiveColorClass = () => {
    colorPalette.forEach(colorPicker => {
        colorPicker.classList.remove('active');
    })
}


        //Change primary colors
colorPalette.forEach(color =>{
    color.addEventListener('click', () =>{
        let primary;

        //remove active class on colors
        changeActiveColorClass();


        if(color.classList.contains('color-1')){
            primaryHue = 252;
        }else if(color.classList.contains('color-2')){
            primaryHue = 52;
        }else if(color.classList.contains('color-3')){
            primaryHue = 352;
        }else if(color.classList.contains('color-4')){
            primaryHue = 152;
        }else if(color.classList.contains('color-5')){
            primaryHue = 202;
        }
        color.classList.add('active');
        root.style.setProperty('--primary-color-hue', primaryHue);
    })
});

// theme Background values
let lightColorLightness;
let whiteColorLightnees;
let darkColorLightness;

// changes background color
const changeBG = () => {
    root.style.setProperty('--light-color-lightness', lightColorLightness);
    root.style.setProperty('--white-color-lightness', whiteColorLightnees);
    root.style.setProperty('--dark-color-lightness', darkColorLightness);
}
// change background colors
Bg1.addEventListener('click', () => {
    // add active class
    Bg1.classList.add('active');
    //remove acive class
    Bg2.classList.remove('active');
    Bg3.classList.remove('active');
    //remove customized changes from locall storage
    window.location.reload();
});
Bg2.addEventListener('click', () => {
    darkColorLightness = '95%';
    whiteColorLightnees = '20%';
    lightColorLightness = '15%';

    // add active class
    Bg2.classList.add('active');
    // remove active class from the others
    Bg1.classList.remove('active');
    Bg3.classList.remove('active');
    changeBG();
});

Bg3.addEventListener('click', () => {
    darkColorLightness = '95%';
    whiteColorLightnees = '10%';
    lightColorLightness = '0%';

    // add active class
    Bg3.classList.add('active');
    //remove active class from others
    Bg1.classList.remove('active');
    Bg2.classList.remove('active');
    changeBG();
})
// end
//liking system
//liking system

$(document).ready(function() {
    // Loop through each like button
    $('.like-buttons').each(function() {
        var button = $(this);
        var postID = button.data('post-id');
        
        // Define a function to handle the AJAX request and button behavior
        function checkLikeStatus(postID, button) {
            $.ajax({
                type: 'GET',
                url: 'check_like_status.php',
                data: { post_id: postID },
                success: function(data) {
                    if (data === 'liked') {
                        button.addClass('liked');
                    }
                }
            });
        }
        
        // Call the function with the current postID and button
        checkLikeStatus(postID, button);
    });
});

$(document).on('click', '.like-buttons', function() {
    var button = $(this);
    var postID = button.data('post-id');
    var postType = button.data('post-type');

    var url;
    if (postType === 'text') {
        url = 'save_text_post_like.php';
    } else if (postType === 'image' || postType === 'video') {
        url = 'save_picture_like.php';
    }

    $.ajax({
        type: 'POST',
        url: url,
        data: { post_id: postID },
        success: function(data) {
            button.toggleClass('liked'); // Toggle the class immediately
        }
    });
});

// Click event listener for unlike buttons
$(document).on('click', '.liked', function() {
    var button = $(this);
    var postID = button.data('post-id');
    var postType = button.data('post-type');
    
    var url = 'remove_like.php';

    $.ajax({
        type: 'POST',
        url: url,
        data: { post_id: postID, post_type: postType }, // Send post type to identify table
        success: function(data) {
            if (data === 'success') {
                button.removeClass('liked'); // Remove the class immediately
            }
        }
    });
});

$(document).ready(function() {
    // Click event listener for comment buttons
    $(document).on('click', '.comment-buttons', function() {
        var postID = $(this).data('post-id');
        var commentBox = $(this).parent().find('.comments-section');

        // Toggle the visibility of the comment box
        commentBox.toggle();

        // If the comment box is visible, focus on the input field
        if (commentBox.is(':visible')) {
            commentBox.find('.comment-input').focus();
        }
    });

    // Click event listener for post-comment buttons
    $(document).on('click', '.post-comment', function() {
        var feedContainer = $(this).closest('.feed');
        var postID = feedContainer.data('post-id');
        var postType = feedContainer.data('post-type');
        var commentInput = $(this).siblings('.comment-input');
        var commentText = commentInput.val().trim(); // Get the comment text

        // Check if the comment text is not empty
        if (commentText !== '') {
            // Debugging: Log the values of postID, postType, and commentText
            console.log('postID:', postID);
            console.log('postType:', postType);
            console.log('commentText:', commentText);
            // Perform an AJAX request to save the comment
            $.ajax({
                type: 'POST',
                url: 'save_comment.php', // Update with your PHP endpoint for saving comments
                data: {
                    post_id: postID,
                    post_type: postType,
                    comment: commentText
                },
                success: function(response) {
                    if (response.status === 'success') {
                        // Comment saved successfully, you can update the UI here
                        // For example, append the new comment to the comments section

                        // Create a new comment element and append it to the comments section
                        var newComment = '<div class="comment">' + commentText + '</div>';
                        $('.comments-section:visible').append(newComment);

                        // Clear the comment input
                        commentInput.val('');
                    } 
                },
                error: function(xhr, status, error) {
                    console.log('AJAX Error: ' + status + ' - ' + error);
                    alert('An error occurred while saving the comment.');
                }
                
            });
        }

        // Close the comment box
        $(this).closest('.comments-section').hide();
    });
});

$(document).ready(function() {

    // click event listener for share buttons
    $(document).on('click', '.share-buttons', function(){
        //get the post link from the data attribute
        var postLink = $(this).data('post-link');
    
    
        // create a hidden input element to hold the post link
        var tempInput = document.createElement('input');
        tempInput.setAttribute('value', postLink);
        document.body.appendChild(tempInput);
    
        // select and copy the link
        tempInput.select();
        document.execCommand('copy');
    
        // remove the temporary input element
        document.body.removeChild(tempInput);
    
        // show a notification or alert to indicate that the  link has been copied
        alert('Post link copied to clipboard: ' +postLink);
    })
});
$(document).ready(function() {
    $('.save-button').click(function() {
        var postID = $(this).data('post-id');
        var postType = $(this).data('post-type');
        var saveButton = $(this);

        $.ajax({
            type: 'POST',
            url: 'save_post.php',
            data: {
                post_id: postID,    // Change 'postID' to 'post_id'
                post_type: postType // Change 'postType' to 'post_type'
            },
            success: function(response) {
                if (response === 'saved') {
                    saveButton.addClass('saved');
                    saveButton.html('<i class="uil uil-bookmark-full"></i>');
                } else if (response === 'unsaved') {
                    saveButton.removeClass('saved');
                    saveButton.html('<i class="uil uil-bookmark"></i>');
                }
            }
        });
    });
});
src="https://code.jquery.com/jquery-3.6.0.min.js">

// Click event listener for "liked by" links
$(document).on('click', '.show-liked-users', function(event) {
    event.preventDefault();
    var postID = $(this).data('post-id');
    var postType = $(this).data('post-type');
    var likesBox = $('#likes-box-' + postID); // Assuming you're using an ID for your likes box

    // Check if the likes box is already populated
    if (likesBox.hasClass('populated')) {
        likesBox.toggle();
    } else {
        // Send an AJAX request to get liked users
        $.ajax({
            type: 'GET',
            url: 'get_liked_users.php',
            data: {
                post_id: postID,
                post_type: postType 
            },
            dataType: 'json',
            success: function(data) {
                if (data.length > 0) {
                    // Clear any existing content in the likes box
                    likesBox.empty();
                    
                    // Loop through the liked users and append them to the likes box
                    for (var i = 0; i < data.length; i++) {
                        var user = data[i];
                        var userElement = '<div class="liked-user">' +
                            '<img src="' + user.profile_image + '" alt="' + user.full_name + '">' +
                            '<span>' + user.full_name + '</span>' +
                            '</div>';
                        likesBox.append(userElement);
                    }

                    // Add a class to indicate that the likes box is populated
                    likesBox.addClass('populated');
                    
                    // Show the likes box
                    likesBox.show();
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.log('AJAX Error:', textStatus, errorThrown);
            }
            
        });
    }
});
