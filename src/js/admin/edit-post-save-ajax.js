/**
 * Save a post without page reload using AJAX
 * @param {MouseEvent} e Click Event received by the listener
 * @param {Boolean} publish Publish if original post status & post status are "draft"
 */
const savePost = (e, publish) => {
  const form = document.querySelector('#post');
  const data = new FormData(form);

  let spinner = publish ? 
    document.querySelector('#publishing-action>.spinner') : 
    document.querySelector('#save-action>.spinner');
  const originalPostStatus = document.querySelector('*[name="original_post_status"]').value;
  const postStatus = document.querySelector('*[name="post_status"]').value;
  if (spinner) spinner.classList.add('is-active');

  if ((publish && postStatus === 'draft' && originalPostStatus === 'draft') || postStatus !== originalPostStatus) return;

  e.preventDefault();

  fetch(form.getAttribute("action"), {
    method: 'POST',
    body: data,
    redirect: 'manual',
  }).then(res => {
    if (spinner) spinner.classList.remove('is-active');
    createNotice('notice-success', 'Page mise à jour.');
  }).catch(err => {
    if (spinner) spinner.classList.remove('is-active');
    createNotice('notice-error', `Une erreur s'est produite lors de la mise à jour de la page.`);
  });
};

/**
 * Creates a notice and displays it below "wp-header-end".
 * @param {String} type Notice type [(See documentation)](https://developer.wordpress.org/reference/hooks/admin_notices/)
 * @param {String} message Notice message to display.
 */
const createNotice = (type, message) => {
  let notice = document.createElement('div');
  notice.classList.add('notice', 'is-dismissible');
  notice.classList.add(type);
  notice.innerHTML = `
    <p>${message}</p>
    <button type="button" class="notice-dismiss">
      <span class="screen-reader-text">Dismiss this notice.</span>
    </button>
  `;
  notice.querySelector('.notice-dismiss').addEventListener('click', () => {
    notice.parentNode.removeChild(notice);
  });
  document.querySelector('.wp-header-end').after(notice);
}

document.getElementById('publish').addEventListener('click', e => { savePost(e, true); });
if (document.getElementById('save-post')) {
  document.getElementById('save-post').addEventListener('click', e => { savePost(e, false); });
}