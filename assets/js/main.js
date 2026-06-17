
document.addEventListener('DOMContentLoaded', function () {
    // =========================
    // AJAX FOLDER LOAD
    // =========================
    const folderLinks = document.querySelectorAll('.folder-list a');
    const dashboardContent = document.getElementById('dashboardContent');

    if (dashboardContent) {

        folderLinks.forEach(link => {

            link.addEventListener('click', async function (event) {

                if (!link.href.includes('dashboard_new.php')) return;

                event.preventDefault();

                const url = new URL(link.href);

                url.searchParams.set('ajax', '1');

                const response = await fetch(url.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (!response.ok) return;

                const html = await response.text();

                dashboardContent.innerHTML = html;

                folderLinks.forEach(item =>
                    item.classList.remove('active')
                );

                link.classList.add('active');
            });

        });
    }


    // =========================
    // VIEW MODE TOGGLE
    // =========================
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const largeGridViewBtn = document.getElementById('largeGridViewBtn');
    const fileContainer = document.getElementById('fileContainer');

    function setViewMode(mode) {
        if (!fileContainer) return;
        fileContainer.classList.toggle('list-view', mode === 'list');
        fileContainer.classList.toggle('grid-view', mode === 'grid');
        fileContainer.classList.toggle('large-grid', mode === 'large');
        if (gridViewBtn) gridViewBtn.classList.toggle('active', mode === 'grid');
        if (listViewBtn) listViewBtn.classList.toggle('active', mode === 'list');
        if (largeGridViewBtn) largeGridViewBtn.classList.toggle('active', mode === 'large');
        // show/hide list-view specific action menus (three-dot menu in list view)
        document.querySelectorAll('.list-view-actions').forEach(el => {
            el.classList.toggle('d-none', mode !== 'list');
        });
        localStorage.setItem('dashboardViewMode', mode);
    }

    const savedMode = localStorage.getItem('dashboardViewMode') || 'grid';
    setViewMode(savedMode);

    if (gridViewBtn) gridViewBtn.addEventListener('click', () => setViewMode('grid'));
    if (listViewBtn) listViewBtn.addEventListener('click', () => setViewMode('list'));
    if (largeGridViewBtn) largeGridViewBtn.addEventListener('click', () => setViewMode('large'));

    const bulkToolbar = document.getElementById('bulkToolbar');
    const masterCheckbox = document.getElementById('masterCheckbox');
    const selectedCount = document.getElementById('selectedCount');
    const downloadSelected = document.getElementById('downloadSelected');
    const shareSelected = document.getElementById('shareSelected');
    const deleteSelected = document.getElementById('deleteSelected');
    const clearSelection = document.getElementById('clearSelection');

    const filterStarred = document.getElementById('filterStarred');
    const typeFilterCheckboxes = document.querySelectorAll('.type-filter');
    const extensionFilterCheckboxes = document.querySelectorAll('.extension-filter');

    // Helper to always query current selects since DOM may change
    const fileSelects = () => document.querySelectorAll('.file-select');

    let currentSelected = new Set();

    function updateSelectionState() {
        const selects = Array.from(fileSelects());
        currentSelected = new Set(selects.filter(cb => cb.checked).map(cb => cb.dataset.itemId));
        if (selectedCount) selectedCount.textContent = currentSelected.size;
        if (bulkToolbar) bulkToolbar.classList.toggle('d-none', currentSelected.size === 0);
        if (masterCheckbox) {
            const visibleCheckboxes = selects.filter(cb => !cb.closest('.file-item').classList.contains('d-none'));
            masterCheckbox.checked = visibleCheckboxes.length > 0 && visibleCheckboxes.every(cb => cb.checked);
        }
        selects.forEach(cb => {
            const item = cb.closest('.file-item');
            if (!item) return;
            item.classList.toggle('selected', cb.checked);
        });
    }

    if (masterCheckbox) {
        masterCheckbox.addEventListener('change', () => {
            Array.from(fileSelects()).forEach(cb => {
                if (cb.closest('.file-item').classList.contains('d-none')) return;
                cb.checked = masterCheckbox.checked;
            });
            updateSelectionState();
        });
    }

    function filterCards() {
        const showStarred = filterStarred?.checked;
        const selectedTypes = [...typeFilterCheckboxes].filter(cb => cb.checked).map(cb => cb.value);
        const selectedExts = [...extensionFilterCheckboxes].filter(cb => cb.checked).map(cb => cb.value.toLowerCase());

        document.querySelectorAll('.file-item').forEach(item => {
            let visible = true;
            const itemStarred = item.dataset.fileStarred === '1';
            const itemType = item.dataset.fileType || '';
            const itemExt = item.dataset.fileExtension || '';

            if (showStarred && !itemStarred) {
                visible = false;
            }
            if (selectedTypes.length && !selectedTypes.includes(itemType)) {
                visible = false;
            }
            if (selectedExts.length && !selectedExts.includes(itemExt)) {
                visible = false;
            }

            item.classList.toggle('d-none', !visible);
        });
    }

    // Listen for changes on any file-select (supports dynamic content)
    document.addEventListener('change', (e) => {
        if (e.target && e.target.classList && e.target.classList.contains('file-select')) {
            updateSelectionState();
        }
    });

    const allFilters = [filterStarred, ...typeFilterCheckboxes, ...extensionFilterCheckboxes].filter(Boolean);
    allFilters.forEach(filter => {
        filter.addEventListener('change', filterCards);
    });

    if (clearSelection) {
        clearSelection.addEventListener('click', () => {
            Array.from(fileSelects()).forEach(cb => cb.checked = false);
            updateSelectionState();
        });
    }

    if (downloadSelected) {
        downloadSelected.addEventListener('click', () => {
            if (!currentSelected.size) return;
            const firstId = [...currentSelected][0];
            const el = document.querySelector(`.file-item[data-item-id="${firstId}"]`);
            const type = el?.dataset?.itemType || 'file';
            if (type === 'folder') {
                window.location.href = `dashboard_new.php?folder_id=${encodeURIComponent(firstId)}`;
            } else {
                window.location.href = `download.php?file_id=${encodeURIComponent(firstId)}`;
            }
        });
    }

    if (shareSelected) {
        shareSelected.addEventListener('click', () => {
            if (!currentSelected.size) return;
            const firstId = [...currentSelected][0];
            const el = document.querySelector(`.file-item[data-item-id="${firstId}"]`);
            const type = el?.dataset?.itemType || 'file';
            if (type === 'folder') {
                alert('Folder sharing from bulk toolbar is not supported. Open folder and share specific items.');
                return;
            }
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'dashboard_new.php';
            form.style.display = 'none';
            form.appendChild(createHiddenInput('file_id', firstId));
            form.appendChild(createHiddenInput('share_file', '1'));
            document.body.appendChild(form);
            form.submit();
        });
    }

    if (deleteSelected) {
        deleteSelected.addEventListener('click', async () => {
            if (!currentSelected.size) return;
            if (!confirm('Delete selected items?')) return;

            for (const id of currentSelected) {
                const el = document.querySelector(`.file-item[data-item-id="${id}"]`);
                const type = el?.dataset?.itemType || 'file';
                const formData = new FormData();
                if (type === 'folder') {
                    formData.append('folder_id', id);
                    formData.append('delete_folder', '1');
                } else {
                    formData.append('file_id', id);
                    formData.append('delete_file', '1');
                }
                await fetch('dashboard_new.php', {
                    method: 'POST',
                    body: formData,
                    redirect: 'follow'
                });
            }
            window.location.reload();
        });
    }

    function setThemeButton() {
        const isDark = localStorage.getItem('darkMode') === 'true';
        if (darkBtn) {
            const icon = darkBtn.querySelector('i');
            if (icon) icon.className = isDark ? 'bx bx-sun' : 'bx bx-moon';
        }
    }

    setThemeButton();

    const uploadButton = document.getElementById('uploadButton');
    if (uploadButton) {
        setInterval(() => {
            uploadButton.classList.toggle('text-hidden');
        }, 2500);
    }

    // =========================
    // UPLOAD FILE SUMMARY
    // =========================
    const selectedFilesInfo = document.getElementById('selectedFilesInfo');
    const folderRelativePathsInput = document.getElementById('folderRelativePaths');
    const fileInput = document.getElementById('fileInput');
    const folderInput = document.getElementById('folderInput');

    function buildFolderRelativePaths(files) {
        if (!files || !files.length) {
            return [];
        }

        return Array.from(files).map(file => {
            return file.webkitRelativePath || file.relativePath || file.name;
        });
    }

    function updateFolderRelativePaths() {
        if (!folderRelativePathsInput) return;
        const paths = buildFolderRelativePaths(folderInput?.files);
        folderRelativePathsInput.value = JSON.stringify(paths);
    }

    function updateSelectedFilesInfo() {
        let text = 'No files selected yet.';
        const fileCount = fileInput?.files?.length || 0;
        const folderCount = folderInput?.files?.length || 0;

        if (fileCount > 0 || folderCount > 0) {
            const parts = [];
            if (fileCount > 0) {
                parts.push(`${fileCount} file${fileCount === 1 ? '' : 's'}`);
            }
            if (folderCount > 0) {
                parts.push(`${folderCount} folder file${folderCount === 1 ? '' : 's'}`);
            }
            text = parts.join(' + ');
        }

        if (selectedFilesInfo) {
            selectedFilesInfo.textContent = text;
        }
    }

    if (fileInput) {
        fileInput.addEventListener('change', updateSelectedFilesInfo);
    }
    if (folderInput) {
        folderInput.addEventListener('change', () => {
            updateSelectedFilesInfo();
            updateFolderRelativePaths();
        });
    }


    // =========================
    // FILE MENU TOGGLE
    // =========================

document.addEventListener("DOMContentLoaded", () => {

    const menuBtn = document.getElementById("mobileMenuBtn");
    const sidebar = document.querySelector(".sidebar");

    if(menuBtn && sidebar){

        menuBtn.addEventListener("click", () => {
            sidebar.classList.toggle("show");
        });

    }

});


    document.addEventListener('click', () => {

        document.querySelectorAll('.file-menu').forEach(menu => {

            menu.classList.remove('active');

        });

    });


    // =========================
    // DRAG & DROP UPLOAD
    // =========================
    const dropArea = document.getElementById("dropArea");
    const uploadForm = document.getElementById('uploadForm');

    if (dropArea && fileInput && folderInput && uploadForm) {

        dropArea.addEventListener("click", () => {
            fileInput.click();
        });

        dropArea.addEventListener("dragover", (e) => {

            e.preventDefault();

            dropArea.classList.add("dragover");

        });

        dropArea.addEventListener("dragleave", () => {

            dropArea.classList.remove("dragover");

        });

        dropArea.addEventListener("drop", (e) => {

            e.preventDefault();

            fileInput.files = e.dataTransfer.files;

            dropArea.classList.remove("dragover");

            simulateUpload();

        });

        fileInput.addEventListener("change", () => {

            simulateUpload();

        });

    }


    // =========================
    // DARK MODE
    // =========================
    const darkBtn =
        document.getElementById("darkModeToggle");

    if (darkBtn) {

        darkBtn.addEventListener("click", () => {

            document.body.classList.toggle("dark-mode");

            localStorage.setItem(
                "darkMode",
                document.body.classList.contains("dark-mode")
            );

            setThemeButton();

        });

    }

    if (localStorage.getItem("darkMode") === "true") {

        document.body.classList.add("dark-mode");

    }

    setThemeButton();


    // =========================
    // COPY SHARE LINK
    // =========================
    window.copyShareLink = function () {

        const input =
            document.getElementById('shareInput');

        input.select();

        input.setSelectionRange(0, 99999);

        navigator.clipboard.writeText(input.value);

        alert('Link copied successfully!');

    };


    // =========================
    // UPLOAD PROGRESS
    // =========================
    function simulateUpload() {

        const progress =
            document.getElementById("uploadProgress");

        if (!progress) return;

        let width = 0;

        const interval = setInterval(() => {

            width += 10;

            progress.style.width = width + "%";

            progress.innerHTML = width + "%";

            if (width >= 100) {

                clearInterval(interval);

            }

        }, 120);

    }

});


// =========================
// HELPERS
// =========================
function createHiddenInput(name, value) {

    const input = document.createElement('input');

    input.type = 'hidden';

    input.name = name;

    input.value = value;

    return input;

}


// =========================
// RENAME FILE
// =========================
window.promptRename = function (fileId, fileName) {

    const newName =
        prompt('Enter new name:', fileName);

    if (!newName) return;

    const form = document.createElement('form');

    form.method = 'POST';

    form.action = 'dashboard_new.php';

    form.style.display = 'none';

    form.appendChild(createHiddenInput('file_id', fileId));

    form.appendChild(createHiddenInput('new_name', newName));

    form.appendChild(createHiddenInput('rename_file', '1'));

    document.body.appendChild(form);

    form.submit();

};

window.toggleRenameForm = function (fileId) {
    const form = document.getElementById('renameForm' + fileId);
    if (!form) return;
    form.classList.toggle('d-none');
    if (!form.classList.contains('d-none')) {
        const input = form.querySelector('input[name="new_name"]');
        if (input) {
            input.focus();
            input.select();
        }
    }
};

// Quick action buttons (download / share) - delegated
document.addEventListener('click', function (e) {
    const dl = e.target.closest('.quick-download');
    if (dl) {
        const id = dl.dataset.fileId;
        if (id) window.location.href = `download.php?file_id=${encodeURIComponent(id)}`;
    }

    const sh = e.target.closest('.quick-share');
    if (sh) {
        const id = sh.dataset.fileId;
        if (!id) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'dashboard_new.php';
        form.style.display = 'none';
        form.appendChild(createHiddenInput('file_id', id));
        form.appendChild(createHiddenInput('share_file', '1'));
        document.body.appendChild(form);
        form.submit();
    }
});


// =========================
// MOVE FILE
// =========================
window.promptMove = function (fileId) {
    const folderId = prompt('Enter target folder ID:');
    if (folderId === null) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'dashboard_new.php';
    form.style.display = 'none';
    form.appendChild(createHiddenInput('file_id', fileId));
    form.appendChild(createHiddenInput('target_folder_id', folderId));
    form.appendChild(createHiddenInput('move_file', '1'));
    document.body.appendChild(form);
    form.submit();
};

window.promptMoveFolder = function (folderId) {
    const targetFolderId = prompt('Enter the ID of the destination folder, or leave blank for root:');
    if (targetFolderId === null) return;

    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'dashboard_new.php';
    form.style.display = 'none';
    form.appendChild(createHiddenInput('folder_id', folderId));
    form.appendChild(createHiddenInput('target_parent_id', targetFolderId));
    form.appendChild(createHiddenInput('move_folder', '1'));
    document.body.appendChild(form);
    form.submit();
};

window.copyFolder = function (folderId) {
    alert('Copy folder is not implemented yet. Use move or create a duplicate folder manually.');
};

window.copyFile = function (fileId) {
    alert('Copy file is not implemented yet. This is a placeholder for future copy support.');
};

window.showDetails = function (item) {
    if (!item) return;
    const type = item.dataset.itemType;
    const name = item.dataset.fileName || item.dataset.folderName;
    const size = item.dataset.fileSize || item.dataset.folderSize;
    const modified = item.dataset.fileDate || item.dataset.folderCreated;
    const owner = item.dataset.fileOwner || item.dataset.folderOwner;

    alert(`Type: ${type}\nName: ${name}\nSize: ${size ? size + ' bytes' : 'Unknown'}\nModified: ${modified || 'Unknown'}\nOwner: ${owner || 'Unknown'}`);
};

// =========================
// SHARE FILE
// =========================
window.promptShare = function (fileId) {

    const form = document.createElement('form');

    form.method = 'POST';

    form.action = 'dashboard_new.php';

    form.style.display = 'none';

    form.appendChild(createHiddenInput('file_id', fileId));

    form.appendChild(createHiddenInput('share_file', '1'));

    document.body.appendChild(form);

    form.submit();

};


// =========================
// RENAME FOLDER
// =========================
window.renameFolder = function (id, oldName) {

    const newName =
        prompt("Rename folder:", oldName);

    if (!newName) return;

    const form = document.createElement('form');

    form.method = 'POST';

    form.action = 'dashboard_new.php';

    form.appendChild(createHiddenInput('folder_id', id));

    form.appendChild(createHiddenInput('new_name', newName));

    form.appendChild(createHiddenInput('rename_folder', '1'));

    document.body.appendChild(form);

    form.submit();

};

setTimeout(() => {

    document.querySelectorAll('.alert').forEach(alert => {

        alert.classList.remove('show');

    });

}, 3000);

// Sidebar & upload small helpers
document.addEventListener('DOMContentLoaded', function () {
    const sidebarToggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    const searchInput = document.querySelector('.navbar .form-control[type="search"]');

    sidebarToggleBtn?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        sidebar?.classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (!sidebar || !sidebar.classList.contains('show')) return;
        if (!sidebar.contains(e.target) && !e.target.closest('#sidebarToggle')) {
            sidebar.classList.remove('show');
        }
    });

    // keep the search icon inside the input-group (modern UI)

    // Restore animated placeholder text for the search input
    const searchPhrases = [
        'Search files, folders...',
        'Find documents fast...',
        'Search your Go...'
    ];
    let phraseIndex = 0;
    let charIndex = 0;
    let typingTimeout;

    function typeSearchPlaceholder() {
        if (!searchInput || document.activeElement === searchInput || searchInput.value) return;
        const phrase = searchPhrases[phraseIndex];

        if (charIndex <= phrase.length) {
            searchInput.placeholder = phrase.slice(0, charIndex);
            charIndex += 1;
            typingTimeout = window.setTimeout(typeSearchPlaceholder, 80);
            return;
        }

        window.setTimeout(() => {
            charIndex = 0;
            phraseIndex = (phraseIndex + 1) % searchPhrases.length;
            typeSearchPlaceholder();
        }, 1500);
    }

    function resetSearchPlaceholder() {
        if (!searchInput) return;
        clearTimeout(typingTimeout);
        if (searchInput.value) {
            searchInput.placeholder = '';
            return;
        }
        charIndex = 0;
        typingTimeout = window.setTimeout(typeSearchPlaceholder, 300);
    }

    if (searchInput) {
        searchInput.placeholder = '';
        typeSearchPlaceholder();
        searchInput.addEventListener('focus', () => {
            clearTimeout(typingTimeout);
            if (!searchInput.value) {
                searchInput.placeholder = searchPhrases[phraseIndex].slice(0, Math.max(1, charIndex));
            }
        });
        searchInput.addEventListener('blur', resetSearchPlaceholder);
        searchInput.addEventListener('input', () => {
            if (searchInput.value) {
                searchInput.placeholder = '';
                clearTimeout(typingTimeout);
            } else {
                resetSearchPlaceholder();
            }
        });
    }

    // Upload dropdown file selector handling
    document.addEventListener('click', function (e) {
        const button = e.target.closest('.upload-option');
        if (!button) return;

        const uploadModal = document.getElementById('uploadModal');
        const fileInput = uploadModal?.querySelector('#fileInput');
        const folderInput = uploadModal?.querySelector('#folderInput');

        if (uploadModal && window.bootstrap) {
            const modalInstance = bootstrap.Modal.getOrCreateInstance(uploadModal);
            modalInstance.show();
        }

        if (button.dataset.uploadType === 'files') {
            fileInput?.click();
        }

        if (button.dataset.uploadType === 'folder') {
            folderInput?.click();
        }
    });
});




function scanUnavailable(){
    alert("This feature is unavailable for now");
}


// VIEW SWITCH

const container = document.getElementById("fileContainer");

const gridBtn = document.getElementById("gridViewBtn");
const listBtn = document.getElementById("listViewBtn");
const largeGridBtn = document.getElementById("largeGridViewBtn");


if(gridBtn){

gridBtn.onclick = ()=>{

container.className =
"row g-4 file-container grid-view";

document.querySelectorAll(".file-item")
.forEach(item=>{

item.className =
"file-item col-xl-3 col-lg-4 col-md-6 col-12";

});
}
}


if(largeGridBtn){

largeGridBtn.onclick = ()=>{


container.className =
"row g-4 file-container large-grid-view";


document.querySelectorAll(".file-item")
.forEach(item=>{

item.className =
"file-item col-xl-4 col-lg-6 col-12";

});


}

}




if(listBtn){

listBtn.onclick = ()=>{


container.className =
"file-container list-view";


document.querySelectorAll(".file-item")
.forEach(item=>{

item.className =
"file-item list-item";


});


}


}


/*/toast notification*/

function scanUnavailable(){


    let toast = document.getElementById("toastMessage");


    toast.classList.add("show");



    setTimeout(()=>{


        toast.classList.remove("show");


    },2000);



}

