<div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm-down">
    <form method="POST" id="deleteForm" class="modal-content confirm-modal">
      @csrf
      @method('DELETE')
      <div class="modal-body text-center p-4 p-md-5">
        <div class="confirm-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
        <h5 class="fw-bold mb-2">Are you sure?</h5>
        <p class="text-muted mb-1">Is this delete?</p>
        <p class="fw-semibold mb-4" id="deleteName"></p>
        <div class="d-flex gap-2 justify-content-center">
          <button type="button" class="btn btn-light px-4" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-danger px-4">Yes, delete</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
  const deleteModal = document.getElementById('deleteModal');
  const deleteForm  = document.getElementById('deleteForm');
  const deleteBtn   = deleteForm.querySelector('button[type="submit"]');

  deleteModal.addEventListener('show.bs.modal', function (e) {
    const btn = e.relatedTarget;
    deleteForm.action = btn.dataset.action;
    document.getElementById('deleteName').textContent = btn.dataset.name;
    deleteBtn.disabled = false;
  });

  deleteForm.addEventListener('submit', function () {
    deleteBtn.disabled = true;   
  });
</script>

