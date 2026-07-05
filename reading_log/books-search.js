(function () {
  const keywordInput = document.getElementById('book-keyword');
  const searchButton = document.getElementById('book-search-button');
  const message = document.getElementById('book-search-message');
  const results = document.getElementById('book-results');
  const titleInput = document.getElementById('title');
  const authorInput = document.getElementById('author');
  const themeInput = document.getElementById('theme');
  const priceInput = document.getElementById('price');
  const recoveryInput = document.getElementById('recovery_amount');
  const returnDueDateInput = document.getElementById('return_due_date');
  const pageCountInput = document.getElementById('page_count');
  const methodInput = document.getElementById('acquisition_method');
  const exitActionInput = document.getElementById('exit_action');
  const statusInput = document.getElementById('status');
  const coverInput = document.getElementById('cover_image');
  const acquisitionPanel = document.getElementById('acquisition-panel');
  const amazonLink = document.getElementById('amazon-link');
  const kindleLink = document.getElementById('kindle-link');
  const libraryLink = document.getElementById('library-link');
  const mercariLink = document.getElementById('mercari-link');
  const methodButtons = document.querySelectorAll('.method-button');

  if (!keywordInput || !searchButton || !message || !results || !titleInput || !authorInput || !coverInput) {
    return;
  }

  const categoryMap = [
    ['Business', 'ビジネス'],
    ['Economics', 'ビジネス'],
    ['Self-Help', '自己啓発'],
    ['Fiction', '小説'],
    ['Comics', 'マンガ'],
    ['Computers', 'IT・プログラミング'],
    ['Education', '教育'],
    ['Psychology', '心理'],
    ['Health', '健康'],
    ['History', '歴史'],
    ['Biography', '伝記'],
    ['Design', 'デザイン'],
    ['Art', 'アート'],
    ['Cooking', '料理'],
    ['Travel', '旅行'],
    ['Science', '科学'],
    ['Technology', 'テクノロジー']
  ];

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

  function normalizeCategory(category, title, description) {
    const text = [category, title, description].join(' ');
    for (const [keyword, label] of categoryMap) {
      if (text.toLowerCase().includes(keyword.toLowerCase())) {
        return label;
      }
    }
    if (/習慣|人生|生き方|自己|成功/.test(text)) return '自己啓発';
    if (/仕事|経営|マーケ|会計|投資|お金/.test(text)) return 'ビジネス';
    if (/小説|物語|文学/.test(text)) return '小説';
    if (/AI|プログラミング|コード|データ/.test(text)) return 'IT・プログラミング';
    return category || '';
  }

  function getBookPrice(saleInfo) {
    if (!saleInfo) return '';
    const price = saleInfo.listPrice || saleInfo.retailPrice;
    if (!price || typeof price.amount !== 'number') return '';
    return Math.round(price.amount);
  }

  function createMetaChip(text) {
    return text ? '<span class="book-meta-chip">' + text + '</span>' : '';
  }

  function updateAcquisitionLinks(title, authors) {
    if (!acquisitionPanel) return;

    const keyword = [title, authors].filter(Boolean).join(' ');
    const encoded = encodeURIComponent(keyword);

    if (amazonLink) amazonLink.href = 'https://www.amazon.co.jp/s?k=' + encoded;
    if (kindleLink) kindleLink.href = 'https://www.amazon.co.jp/s?k=' + encoded + '&i=digital-text';
    if (libraryLink) libraryLink.href = 'https://calil.jp/search?q=' + encoded;
    if (mercariLink) mercariLink.href = 'https://jp.mercari.com/search?keyword=' + encoded;

    acquisitionPanel.hidden = false;
  }

  function chooseMethod(method, fixedPrice) {
    if (methodInput) {
      methodInput.value = method;
    }

    if (typeof fixedPrice === 'string' && priceInput) {
      priceInput.value = fixedPrice;
    }

    if (recoveryInput && method === '図書館') {
      recoveryInput.value = '0';
    }

    if (exitActionInput) {
      if (method === '図書館') {
        exitActionInput.value = '図書館に返す';
      } else if (method === 'Kindle') {
        exitActionInput.value = 'デジタルで保管';
      } else {
        exitActionInput.value = '未定';
      }
    }

    if (statusInput && statusInput.value === '読了') {
      statusInput.value = '未読';
    }

    if (method === '図書館') {
      if (returnDueDateInput) {
        returnDueDateInput.focus();
      }
      setMessage('図書館で登録する準備をしました。返却日も入れると、ダッシュボードでリマインドできます。', false);
      return;
    }

    setMessage(method + 'で登録する準備をしました。読後に売る場合は、あとで回収額も入れてください。', false);
  }

  function createBookCard(book) {
    const info = book.volumeInfo || {};
    const saleInfo = book.saleInfo || {};
    const title = info.title || 'タイトル不明';
    const authors = Array.isArray(info.authors) && info.authors.length > 0
      ? info.authors.join('、')
      : '著者不明';
    const thumbnail = info.imageLinks && (info.imageLinks.thumbnail || info.imageLinks.smallThumbnail)
      ? (info.imageLinks.thumbnail || info.imageLinks.smallThumbnail).replace('http://', 'https://')
      : '';
    const description = info.description ? stripHtml(info.description).slice(0, 120) : '';
    const rawCategory = Array.isArray(info.categories) && info.categories.length > 0 ? info.categories[0] : '';
    const category = normalizeCategory(rawCategory, title, description);
    const price = getBookPrice(saleInfo);
    const pageCount = Number.isInteger(info.pageCount) ? info.pageCount : 0;

    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'book-result';
    button.setAttribute('aria-label', title + 'を入力する');

    const cover = thumbnail
      ? '<img src="' + thumbnail + '" alt="">'
      : '<div class="book-cover-placeholder">No Image</div>';
    const priceText = price ? price.toLocaleString() + '円' : '価格情報なし';

    button.innerHTML =
      '<div class="book-cover">' + cover + '</div>' +
      '<div class="book-result-body">' +
      '<h3></h3>' +
      '<p class="book-author"></p>' +
      '<div class="book-meta-chips">' +
      createMetaChip(category ? 'テーマ: ' + category : 'テーマ情報なし') +
      createMetaChip('価格: ' + priceText) +
      createMetaChip(pageCount ? 'ページ: ' + pageCount.toLocaleString() + 'p' : 'ページ情報なし') +
      '</div>' +
      '</div>';

    button.querySelector('h3').textContent = title;
    button.querySelector('.book-author').textContent = authors;
    const coverImage = button.querySelector('.book-cover img');
    if (coverImage) {
      coverImage.addEventListener('error', function () {
        const coverBox = coverImage.closest('.book-cover');
        if (coverBox) {
          coverBox.innerHTML = '<div class="book-cover-placeholder">表紙なし</div>';
        }
      });
    }

    button.addEventListener('click', function () {
      titleInput.value = title;
      authorInput.value = authors;
      coverInput.value = thumbnail;

      if (themeInput) {
        themeInput.value = category;
      }

      if (priceInput && price) {
        priceInput.value = price;
      }

      if (pageCountInput && pageCount) {
        pageCountInput.value = pageCount;
      }

      updateAcquisitionLinks(title, authors);

      titleInput.focus();
      setMessage('本を選びました。入口を選び、読後に残すか売るかも決められます。', false);
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
      setMessage('検索結果から本を選ぶと、Book Bankに入れる準備ができます。', false);
    } catch (error) {
      setMessage('本の検索に失敗しました。APIキーや許可URLを確認してください。', true);
      console.error(error);
    } finally {
      searchButton.disabled = false;
    }
  }

  searchButton.addEventListener('click', searchBooks);
  methodButtons.forEach(function (button) {
    button.addEventListener('click', function () {
      chooseMethod(button.dataset.method || 'その他', button.dataset.price);
    });
  });

  keywordInput.addEventListener('keydown', function (event) {
    if (event.key === 'Enter') {
      event.preventDefault();
      searchBooks();
    }
  });
})();
