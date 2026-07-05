(function () {
  const keywordInput = document.getElementById('book-keyword');
  const searchButton = document.getElementById('book-search-button');
  const message = document.getElementById('book-search-message');
  const results = document.getElementById('book-results');
  const titleInput = document.getElementById('title');
  const authorInput = document.getElementById('author');
  const memoInput = document.getElementById('memo');
  const coverInput = document.getElementById('cover_image');
  const selectedCover = document.getElementById('selected-cover');
  const selectedCoverImage = document.getElementById('selected-cover-image');

  if (!keywordInput || !searchButton || !message || !results || !titleInput || !authorInput || !coverInput) {
    return;
  }

  function setMessage(text, isError) {
    message.textContent = text;
    message.classList.toggle('is-error', Boolean(isError));
  }

  function clearResults() {
    results.innerHTML = '';
  }

  function stripHtml(value) {
    const temp = document.createElement('div');
    temp.innerHTML = value || '';
    return temp.textContent || temp.innerText || '';
  }

  function createBookCard(book) {
    const info = book.volumeInfo || {};
    const title = info.title || 'タイトル不明';
    const authors = Array.isArray(info.authors) && info.authors.length > 0
      ? info.authors.join('、')
      : '著者不明';
    const thumbnail = info.imageLinks && (info.imageLinks.thumbnail || info.imageLinks.smallThumbnail)
      ? (info.imageLinks.thumbnail || info.imageLinks.smallThumbnail).replace('http://', 'https://')
      : '';
    const description = info.description ? stripHtml(info.description).slice(0, 120) : '';

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'book-result';
    button.setAttribute('aria-label', title + 'を入力する');

    const cover = thumbnail
      ? '<img src="' + thumbnail + '" alt="">'
      : '<div class="book-cover-placeholder">No Image</div>';

    button.innerHTML =
      '<div class="book-cover">' + cover + '</div>' +
      '<div class="book-result-body">' +
      '<h3></h3>' +
      '<p class="book-author"></p>' +
      (description ? '<p class="book-description"></p>' : '') +
      '</div>';

    button.querySelector('h3').textContent = title;
    button.querySelector('.book-author').textContent = authors;
    const descriptionNode = button.querySelector('.book-description');
    if (descriptionNode) {
      descriptionNode.textContent = description;
    }

    // 検索結果をクリックしたら、フォームへタイトル・著者・表紙URLを入れます。
    button.addEventListener('click', function () {
      titleInput.value = title;
      authorInput.value = authors;
      coverInput.value = thumbnail;

      if (selectedCover && selectedCoverImage && thumbnail) {
        selectedCoverImage.src = thumbnail;
        selectedCover.hidden = false;
      } else if (selectedCover) {
        selectedCover.hidden = true;
      }

      if (memoInput && description && memoInput.value.trim() === '') {
        memoInput.value = description;
      }

      titleInput.focus();
      setMessage('選んだ本をフォームに入力しました。', false);
    });

    return button;
  }
  async function searchBooks() {
    const keyword = keywordInput.value.trim();
    const apiKey = window.GOOGLE_BOOKS_API_KEY || '';

    clearResults();

    if (!keyword) {
      setMessage('検索キーワードを入力してください。', true);
      keywordInput.focus();
      return;
    }

    if (!apiKey || apiKey === 'YOUR_GOOGLE_BOOKS_API_KEY') {
      setMessage('books-config.js にGoogle Books APIキーを設定してください。', true);
      return;
    }

    setMessage('検索しています...', false);
    searchButton.disabled = true;

    try {
      const params = new URLSearchParams({
        q: keyword,
        key: apiKey,
        country: 'JP',
        langRestrict: 'ja',
        maxResults: '8',
        printType: 'books'
      });
      // fetchでGoogle Books APIへリクエストを送ります。
      const response = await fetch('https://www.googleapis.com/books/v1/volumes?' + params.toString());

      if (!response.ok) {
        throw new Error('Books API error: ' + response.status);
      }

      const data = await response.json();
      const items = Array.isArray(data.items) ? data.items : [];

      if (items.length === 0) {
        setMessage('該当する本が見つかりませんでした。キーワードを変えて試してください。', false);
        return;
      }

      const fragment = document.createDocumentFragment();
      items.forEach(function (item) {
        fragment.appendChild(createBookCard(item));
      });
      results.appendChild(fragment);
      setMessage('検索結果から本を選ぶと、フォームに自動入力されます。', false);
    } catch (error) {
      setMessage('本の検索に失敗しました。APIキーや許可URLを確認してください。', true);
      console.error(error);
    } finally {
      searchButton.disabled = false;
    }
  }

  searchButton.addEventListener('click', searchBooks);
  keywordInput.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      searchBooks();
    }
  });
})();


