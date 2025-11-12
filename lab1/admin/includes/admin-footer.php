</div><!-- /.main-content -->
            </div><!-- /.content -->
        </div><!-- /.row -->
    </div><!-- /.container-fluid -->
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        // Handle responsive sidebar toggle
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 768) {
                document.querySelector('.sidebar').style.width = '0';
                document.querySelector('.content').style.marginLeft = '0';
            }
            
            // Add mobile toggle button if needed
            if (!document.querySelector('.mobile-toggle')) {
                const header = document.querySelector('.header');
                const mobileToggle = document.createElement('button');
                mobileToggle.className = 'mobile-toggle btn btn-sm btn-outline-secondary d-md-none';
                mobileToggle.innerHTML = '<i class="fas fa-bars"></i>';
                mobileToggle.style.marginRight = '10px';
                
                if (window.innerWidth <= 768) {
                    header.prepend(mobileToggle);
                    
                    mobileToggle.addEventListener('click', function() {
                        const sidebar = document.querySelector('.sidebar');
                        const content = document.querySelector('.content');
                        
                        if (sidebar.style.width === '0px' || !sidebar.style.width) {
                            sidebar.style.width = '260px';
                            sidebar.style.display = 'block';
                            sidebar.style.position = 'fixed';
                            sidebar.style.zIndex = '1000';
                            sidebar.style.top = '0';
                            sidebar.style.left = '0';
                            sidebar.style.height = '100%';
                            sidebar.style.overflowY = 'auto';
                        } else {
                            sidebar.style.width = '0';
                            setTimeout(function() {
                                sidebar.style.display = 'none';
                            }, 300);
                        }
                    });
                }
            }
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>