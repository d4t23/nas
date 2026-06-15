
document.addEventListener('DOMContentLoaded', function () {
    // =========================
    // AJAX FOLDER LOAD
    // =========================
    const folderLinks = document.querySelectorAll('.folder-list a');
    const dashboardContent = document.getElementById('dashboardContent');

    if (dashboardContent) {

        folderLinks.forEach(link => {

            link.addEventListener('click', async function (event) {

                if (!link.href.includes('dashboard.php')) return;

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
                // navigate into folder
                window.location.href = `dashboard.php?folder_id=${encodeURIComponent(firstId)}`;
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
            form.action = 'dashboard.php';
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
                await fetch('dashboard.php', {
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
    const folderInput = document.getElementById('folderInput');

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
                parts.push(`${folderCount} file${folderCount === 1 ? '' : 's'} from folder`);
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
        folderInput.addEventListener('change', updateSelectedFilesInfo);
    }


    // =========================
    // FILE MENU TOGGLE
    // =========================
    document.querySelectorAll('.menu-btn').forEach(btn => {

        btn.addEventListener('click', function (e) {

            e.stopPropagation();

            document.querySelectorAll('.file-menu').forEach(menu => {

                if (menu !== this.parentElement) {

                    menu.classList.remove('active');

                }

            });

            this.parentElement.classList.toggle('active');

        });

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
    const fileInput = document.getElementById("fileInput");

    if (dropArea && fileInput) {

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

    form.action = 'dashboard.php';

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
        form.action = 'dashboard.php';
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

    const folderId =
        prompt('Enter folder ID:');

    if (folderId === null) return;

    const form = document.createElement('form');

    form.method = 'POST';

    form.action = 'dashboard.php';

    form.style.display = 'none';

    form.appendChild(createHiddenInput('file_id', fileId));

    form.appendChild(createHiddenInput('target_folder_id', folderId));

    form.appendChild(createHiddenInput('move_file', '1'));

    document.body.appendChild(form);

    form.submit();

};


// =========================
// SHARE FILE
// =========================
window.promptShare = function (fileId) {

    const form = document.createElement('form');

    form.method = 'POST';

    form.action = 'dashboard.php';

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

    form.action = 'dashboard.php';

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

    sidebarToggleBtn?.addEventListener('click', () => {
        sidebar?.classList.toggle('show');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', (e) => {
        if (!sidebar) return;
        if (!sidebar.classList.contains('show')) return;
        if (!sidebar.contains(e.target) && !e.target.closest('#sidebarToggle')) {
            sidebar.classList.remove('show');
        }
    });

    // Make dropdown uploads act smoothly
    const uploadDropdown = document.getElementById('uploadDropdownBtn');
    document.addEventListener('click', (e) => {
        if (e.target.closest('#uploadDropdownBtn')) return;
        // close bootstrap dropdowns by triggering body click
    });
});
