<div class="modal fade" id="createFolderModal" tabindex="-1" aria-labelledby="createFolderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="dashboard_new.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="createFolderModalLabel">Create Folder</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="folder_name" class="form-label">Folder name</label>
                        <input type="text" name="folder_name" id="folder_name" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="create_folder" class="btn btn-primary">Create</button>
                </div>
            </form>
        </div>
    </div>
</div>
