$(document).ready(function() {
    // Loading animation
    const dashboardCards = document.querySelectorAll('.card-dashboard');
    dashboardCards.forEach(card => {
        card.innerHTML += '<div class="spinner-border spinner-border-sm text-primary position-absolute top-0 end-0 m-3" role="status"><span class="visually-hidden">Loading...</span></div>';
    });

    // Load dashboard data
    $.get('api/dashboard.php', function(data) {
        // Remove spinners
        document.querySelectorAll('.spinner-border').forEach(spinner => {
            spinner.remove();
        });

        // Update dashboard counters with animation
        animateCounter('totalCourses', 0, data.totalCourses);
        animateCounter('totalStudents', 0, data.totalStudents);
        animateCounter('activeEnrollments', 0, data.activeEnrollments);
        animateCounter('totalCertificates', 0, data.totalCertificates);
        
        // Recent courses
        let coursesHtml = '';
        if (data.recentCourses.length === 0) {
            coursesHtml = '<tr><td colspan="3" class="text-center">Nenhum curso cadastrado</td></tr>';
        } else {
            data.recentCourses.forEach(function(course) {
                coursesHtml += `
                    <tr>
                        <td>${course.name}</td>
                        <td>${course.start_date}</td>
                        <td><span class="badge bg-${course.status === 'active' ? 'success' : 'secondary'}">${course.status === 'active' ? 'Ativo' : 'Inativo'}</span></td>
                    </tr>
                `;
            });
        }
        $('#recentCourses').html(coursesHtml);
        
        // Recent enrollments
        let enrollmentsHtml = '';
        if (data.recentEnrollments.length === 0) {
            enrollmentsHtml = '<tr><td colspan="3" class="text-center">Nenhuma matrícula cadastrada</td></tr>';
        } else {
            data.recentEnrollments.forEach(function(enrollment) {
                enrollmentsHtml += `
                    <tr>
                        <td>${enrollment.student_name}</td>
                        <td>${enrollment.course_name}</td>
                        <td>${enrollment.enrollment_date}</td>
                    </tr>
                `;
            });
        }
        $('#recentEnrollments').html(enrollmentsHtml);
    }).fail(function(xhr, status, error) {
        console.error("Erro ao carregar dados do dashboard:", error);
        document.querySelectorAll('.spinner-border').forEach(spinner => {
            spinner.remove();
        });
        
        // Display error message
        $('.card-dashboard .card-body').append('<div class="alert alert-danger mt-2">Erro ao carregar dados</div>');
    });
});

// Function to animate counter
function animateCounter(elementId, start, end) {
    const duration = 1000; // Animation duration in milliseconds
    const frameDuration = 1000/60; // 60fps
    const totalFrames = Math.round(duration / frameDuration);
    const increment = (end - start) / totalFrames;
    
    let currentFrame = 0;
    let currentValue = start;
    const element = document.getElementById(elementId);
    
    const animate = () => {
        currentFrame++;
        currentValue += increment;
        
        if (currentFrame === totalFrames) {
            element.textContent = end;
        } else {
            element.textContent = Math.floor(currentValue);
            requestAnimationFrame(animate);
        }
    };
    
    animate();
}