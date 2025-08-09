/**
 * Voting System JavaScript
 * ระบบช่วยเหลือการลงคะแนนให้ง่ายและสะดวกขึ้น
 */

class VotingSystem {
    constructor() {
        this.totalCriteria = 0;
        this.completedCriteria = 0;
        this.autoSaveInterval = null;
        this.init();
    }

    init() {
        this.setupEventListeners();
        this.loadSavedData();
        this.updateProgress();
        this.setupAutoSave();
        this.setupKeyboardNavigation();
    }

    setupEventListeners() {
        // การลงคะแนน
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', (e) => {
                this.handleScoreChange(e);
                this.updateProgress();
                this.saveToLocalStorage();
                this.animateSelection(e.target);
            });
        });

        // การส่งฟอร์ม
        const form = document.getElementById('votingForm');
        if (form) {
            form.addEventListener('submit', (e) => {
                this.handleFormSubmit(e);
            });
        }

        // ปุ่มลัดสำหรับการลงคะแนน
        this.setupQuickVoting();
    }

    handleScoreChange(event) {
        const section = event.target.closest('.scoring-section');
        const criteriaId = section.dataset.criteriaId;
        
        // อัพเดทสถานะ section
        section.classList.add('completed');
        const icon = section.querySelector('.ti');
        if (icon) {
            icon.className = 'ti ti-check-circle';
        }

        // หา section ถัดไป
        const nextSection = this.findNextIncompleteSection(section);
        if (nextSection) {
            setTimeout(() => {
                this.scrollToSection(nextSection);
                this.focusNextSection(nextSection);
            }, 300);
        }

        // แสดง feedback
        this.showScoreSelectedFeedback(event.target);
    }

    findNextIncompleteSection(currentSection) {
        let nextSection = currentSection.nextElementSibling;
        while (nextSection) {
            if (nextSection.classList.contains('scoring-section') && 
                !nextSection.classList.contains('completed')) {
                return nextSection;
            }
            nextSection = nextSection.nextElementSibling;
        }
        return null;
    }

    scrollToSection(section) {
        section.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'center',
            inline: 'nearest' 
        });
    }

    focusNextSection(section) {
        const firstRadio = section.querySelector('input[type="radio"]');
        if (firstRadio) {
            setTimeout(() => {
                firstRadio.focus();
            }, 500);
        }
    }

    animateSelection(radio) {
        const label = radio.nextElementSibling;
        if (label) {
            label.classList.add('selected-animation');
            setTimeout(() => {
                label.classList.remove('selected-animation');
            }, 300);
        }
    }

    showScoreSelectedFeedback(radio) {
        const section = radio.closest('.scoring-section');
        const feedbackEl = document.createElement('div');
        feedbackEl.className = 'score-feedback';
        feedbackEl.innerHTML = '<i class="ti ti-check"></i> เลือกแล้ว';
        
        section.appendChild(feedbackEl);
        
        setTimeout(() => {
            feedbackEl.classList.add('fade-out');
            setTimeout(() => {
                if (feedbackEl.parentNode) {
                    feedbackEl.parentNode.removeChild(feedbackEl);
                }
            }, 300);
        }, 1500);
    }

    updateProgress() {
        const sections = document.querySelectorAll('.scoring-section');
        this.totalCriteria = sections.length;
        this.completedCriteria = 0;

        sections.forEach(section => {
            const radios = section.querySelectorAll('input[type="radio"]');
            const hasSelected = Array.from(radios).some(radio => radio.checked);
            
            if (hasSelected) {
                this.completedCriteria++;
                section.classList.add('completed');
                const icon = section.querySelector('.ti');
                if (icon) icon.className = 'ti ti-check-circle';
            } else {
                section.classList.remove('completed');
                const icon = section.querySelector('.ti');
                if (icon) icon.className = 'ti ti-circle';
            }
        });

        this.updateProgressBar();
        this.updateSubmitButton();
    }

    updateProgressBar() {
        const percentage = this.totalCriteria > 0 ? 
            Math.round((this.completedCriteria / this.totalCriteria) * 100) : 0;
        
        const progressFill = document.getElementById('progress-fill');
        const progressText = document.getElementById('progress-text');
        
        if (progressFill) {
            progressFill.style.width = percentage + '%';
        }
        
        if (progressText) {
            progressText.textContent = percentage + '%';
        }
    }

    updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;

        const isComplete = this.completedCriteria === this.totalCriteria;
        
        submitBtn.disabled = !isComplete;
        
        if (isComplete) {
            submitBtn.innerHTML = '<i class="ti ti-send me-2"></i>ส่งคะแนนการประเมิน';
            submitBtn.classList.add('btn-ready');
        } else {
            submitBtn.innerHTML = `<i class="ti ti-send me-2"></i>กรุณาลงคะแนนให้ครบ (${this.completedCriteria}/${this.totalCriteria})`;
            submitBtn.classList.remove('btn-ready');
        }
    }

    setupQuickVoting() {
        // สร้างปุ่มลัดสำหรับการลงคะแนนเร็ว
        const quickVotePanel = document.createElement('div');
        quickVotePanel.className = 'quick-vote-panel';
        quickVotePanel.innerHTML = `
            <div class="quick-vote-header">
                <h6><i class="ti ti-zap"></i> ลงคะแนนด่วน</h6>
                <button class="btn-close-quick" onclick="this.parentElement.parentElement.style.display='none'">×</button>
            </div>
            <div class="quick-vote-buttons">
                <button class="btn btn-sm btn-outline-success" onclick="votingSystem.setAllScores('excellent')">
                    ดีมากทั้งหมด
                </button>
                <button class="btn btn-sm btn-outline-primary" onclick="votingSystem.setAllScores('good')">
                    ดีทั้งหมด
                </button>
                <button class="btn btn-sm btn-outline-warning" onclick="votingSystem.setAllScores('fair')">
                    พอใช้ทั้งหมด
                </button>
                <button class="btn btn-sm btn-outline-danger" onclick="votingSystem.clearAllScores()">
                    ล้างทั้งหมด
                </button>
            </div>
        `;
        
        const container = document.querySelector('.voting-form');
        if (container) {
            container.appendChild(quickVotePanel);
        }
    }

    setAllScores(level) {
        if (!confirm('คุณแน่ใจหรือไม่ที่จะตั้งค่าคะแนนทั้งหมด?')) {
            return;
        }

        const sections = document.querySelectorAll('.scoring-section');
        sections.forEach(section => {
            const radios = section.querySelectorAll('input[type="radio"]');
            let targetRadio = null;

            // หาคะแนนที่ตรงกับ level ที่เลือก
            radios.forEach(radio => {
                const label = radio.nextElementSibling;
                if (label) {
                    const text = label.textContent.toLowerCase();
                    switch (level) {
                        case 'excellent':
                            if (text.includes('ดีมาก')) targetRadio = radio;
                            break;
                        case 'good':
                            if (text.includes('ดี') && !text.includes('ดีมาก')) targetRadio = radio;
                            break;
                        case 'fair':
                            if (text.includes('พอใช้')) targetRadio = radio;
                            break;
                    }
                }
            });

            if (targetRadio && !targetRadio.disabled) {
                targetRadio.checked = true;
                this.animateSelection(targetRadio);
            }
        });

        this.updateProgress();
        this.saveToLocalStorage();
    }

    clearAllScores() {
        if (!confirm('คุณแน่ใจหรือไม่ที่จะล้างคะแนนทั้งหมด?')) {
            return;
        }

        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.checked = false;
        });

        this.updateProgress();
        this.clearLocalStorage();
    }

    setupAutoSave() {
        // บันทึกอัตโนมัติทุก 30 วินาที
        this.autoSaveInterval = setInterval(() => {
            this.saveToLocalStorage();
        }, 30000);
    }

    saveToLocalStorage() {
        const data = {};
        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            const name = radio.name;
            const value = radio.value;
            data[name] = value;
        });

        const inventionId = document.querySelector('input[name="invention_id"]')?.value;
        if (inventionId) {
            localStorage.setItem(`voting_${inventionId}`, JSON.stringify(data));
            this.showAutoSaveIndicator();
        }
    }

    loadSavedData() {
        const inventionId = document.querySelector('input[name="invention_id"]')?.value;
        if (!inventionId) return;

        const savedData = localStorage.getItem(`voting_${inventionId}`);
        if (savedData) {
            try {
                const data = JSON.parse(savedData);
                Object.keys(data).forEach(name => {
                    const radio = document.querySelector(`input[name="${name}"][value="${data[name]}"]`);
                    if (radio) {
                        radio.checked = true;
                    }
                });
            } catch (e) {
                console.warn('ไม่สามารถโหลดข้อมูลที่บันทึกไว้ได้');
            }
        }
    }

    clearLocalStorage() {
        const inventionId = document.querySelector('input[name="invention_id"]')?.value;
        if (inventionId) {
            localStorage.removeItem(`voting_${inventionId}`);
        }
    }

    showAutoSaveIndicator() {
        let indicator = document.querySelector('.auto-save-indicator');
        if (!indicator) {
            indicator = document.createElement('div');
            indicator.className = 'auto-save-indicator';
            indicator.innerHTML = '<i class="ti ti-device-floppy"></i> บันทึกอัตโนมัติ';
            document.body.appendChild(indicator);
        }

        indicator.classList.add('show');
        setTimeout(() => {
            indicator.classList.remove('show');
        }, 2000);
    }

    setupKeyboardNavigation() {
        document.addEventListener('keydown', (e) => {
            // Alt + 1-4 สำหรับเลือกคะแนน
            if (e.altKey && e.key >= '1' && e.key <= '4') {
                e.preventDefault();
                this.selectScoreByNumber(parseInt(e.key));
            }
            
            // Arrow keys สำหรับเปลี่ยน section
            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                this.navigateSections(e.key === 'ArrowDown');
            }
        });
    }

    selectScoreByNumber(number) {
        const focusedElement = document.activeElement;
        const section = focusedElement.closest('.scoring-section');
        if (!section) return;

        const radios = section.querySelectorAll('input[type="radio"]');
        if (radios[number - 1] && !radios[number - 1].disabled) {
            radios[number - 1].checked = true;
            radios[number - 1].dispatchEvent(new Event('change'));
        }
    }

    handleFormSubmit(event) {
        event.preventDefault();
        
        const form = event.target;
        const submitBtn = document.getElementById('submitBtn');
        
        if (this.completedCriteria !== this.totalCriteria) {
            alert('กรุณาลงคะแนนให้ครบทุกหัวข้อ');
            return false;
        }

        const confirmMessage = `
            คุณแน่ใจหรือไม่ที่จะส่งคะแนนการประเมิน?
            
            หมายเหตุ: หลังจากส่งแล้วจะไม่สามารถแก้ไขได้
            คะแนนที่ลง: ${this.completedCriteria}/${this.totalCriteria} หัวข้อ
        `;

        if (confirm(confirmMessage)) {
            submitBtn.innerHTML = '<i class="ti ti-loader"></i> กำลังส่งคะแนน...';
            submitBtn.disabled = true;
            
            // ล้างข้อมูลใน localStorage หลังส่งสำเร็จ
            setTimeout(() => {
                this.clearLocalStorage();
                form.submit();
            }, 1000);
        }
    }

    // Methods สำหรับการใช้งานจากภายนอก
    getSummary() {
        const summary = {
            total: this.totalCriteria,
            completed: this.completedCriteria,
            percentage: this.totalCriteria > 0 ? 
                Math.round((this.completedCriteria / this.totalCriteria) * 100) : 0,
            scores: {}
        };

        document.querySelectorAll('input[type="radio"]:checked').forEach(radio => {
            const section = radio.closest('.scoring-section');
            const title = section.querySelector('.section-title').textContent.trim();
            const value = radio.value;
            const label = radio.nextElementSibling.textContent.trim();
            
            summary.scores[title] = {
                value: value,
                label: label
            };
        });

        return summary;
    }

    exportData() {
        const data = this.getSummary();
        const blob = new Blob([JSON.stringify(data, null, 2)], {
            type: 'application/json'
        });
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `voting_data_${Date.now()}.json`;
        a.click();
        URL.revokeObjectURL(url);
    }
}

// สร้าง CSS สำหรับ features เพิ่มเติม
const additionalCSS = `
    .quick-vote-panel {
        position: fixed;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 10px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        padding: 15px;
        z-index: 1000;
        min-width: 250px;
    }

    .quick-vote-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }

    .quick-vote-header h6 {
        margin: 0;
        color: #4f46e5;
    }

    .btn-close-quick {
        background: none;
        border: none;
        font-size: 20px;
        cursor: pointer;
        color: #64748b;
    }

    .quick-vote-buttons {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .score-feedback {
        position: absolute;
        top: 10px;
        right: 10px;
        background: #10b981;
        color: white;
        padding: 5px 10px;
        border-radius: 15px;
        font-size: 12px;
        opacity: 1;
        transition: opacity 0.3s ease;
    }

    .score-feedback.fade-out {
        opacity: 0;
    }

    .auto-save-indicator {
        position: fixed;
        bottom: 20px;
        right: 20px;
        background: #3b82f6;
        color: white;
        padding: 10px 15px;
        border-radius: 20px;
        font-size: 14px;
        opacity: 0;
        transform: translateY(100px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .auto-save-indicator.show {
        opacity: 1;
        transform: translateY(0);
    }

    .selected-animation {
        transform: scale(1.05);
        transition: transform 0.2s ease;
    }

    .btn-ready {
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
        70% { box-shadow: 0 0 0 10px rgba(16, 185, 129, 0); }
        100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
    }

    @media (max-width: 768px) {
        .quick-vote-panel {
            right: 10px;
            top: 10px;
            min-width: 200px;
        }

        .auto-save-indicator {
            bottom: 10px;
            right: 10px;
            font-size: 12px;
            padding: 8px 12px;
        }
    }
`;

// เพิ่ม CSS เข้าไปใน document
const style = document.createElement('style');
style.textContent = additionalCSS;
document.head.appendChild(style);

// สร้าง instance เมื่อ DOM โหลดเสร็จ
let votingSystem;
document.addEventListener('DOMContentLoaded', function() {
    votingSystem = new VotingSystem();
    
    // เพิ่ม global functions สำหรับการเรียกใช้
    window.votingSystem = votingSystem;
});

// Export สำหรับใช้เป็น module (ถ้าต้องการ)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = VotingSystem;
}