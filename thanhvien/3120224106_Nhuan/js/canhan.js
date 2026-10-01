/*
 * canhan.js xử lý các tương tác cho trang cá nhân Phan Nhuận.
 * Chức năng 1: lọc danh sách kỹ năng theo nhóm.
 * Chức năng 2: sao chép email và hiển thị tooltip thông báo.
 * Thử bằng chuột, phím Tab + Enter/Space và kiểm tra ở 360px.
 */

document.addEventListener("DOMContentLoaded", function () {

    // ==========================================
    // 1. LỌC DANH SÁCH KỸ NĂNG THEO NHÓM
    // ==========================================

    const skillsSection = document.querySelector("#skills");
    const skillsList = document.querySelector(".skills-list");

    if (skillsSection && skillsList) {

        const skills =
            skillsList.querySelectorAll("li");

        // Các nhóm kỹ năng
        const groups = [
            {
                name: "Tất cả",
                value: "all"
            },
            {
                name: "HTML & Accessibility",
                value: "html"
            },
            {
                name: "CSS & Responsive",
                value: "css"
            },
            {
                name: "Công cụ & Framework",
                value: "tools"
            }
        ];


        // ------------------------------------------
        // Tạo khu vực chứa các nút lọc
        // ------------------------------------------

        const filterBox =
            document.createElement("div");

        filterBox.classList.add(
            "skill-filter"
        );

        filterBox.setAttribute(
            "role",
            "group"
        );

        filterBox.setAttribute(
            "aria-label",
            "Lọc kỹ năng theo nhóm"
        );


        // ------------------------------------------
        // Tạo tiêu đề cho bộ lọc
        // ------------------------------------------

        const filterTitle =
            document.createElement("span");

        filterTitle.classList.add(
            "skill-filter__title"
        );

        filterTitle.textContent =
            "Lọc kỹ năng:";

        filterBox.appendChild(
            filterTitle
        );


        // ------------------------------------------
        // Tạo các nút lọc
        // ------------------------------------------

        groups.forEach(function (group, index) {

            const button =
                document.createElement("button");

            button.type = "button";

            button.classList.add(
                "skill-filter__button"
            );

            button.textContent =
                group.name;

            button.dataset.group =
                group.value;


            // Nút "Tất cả" được chọn mặc định
            if (index === 0) {

                button.classList.add(
                    "is-active"
                );

                button.setAttribute(
                    "aria-pressed",
                    "true"
                );

            } else {

                button.setAttribute(
                    "aria-pressed",
                    "false"
                );
            }


            // --------------------------------------
            // Khi người dùng bấm nút lọc
            // --------------------------------------

            button.addEventListener(
                "click",
                function () {

                    const selectedGroup =
                        button.dataset.group;


                    // Cập nhật trạng thái nút
                    filterBox
                        .querySelectorAll(
                            ".skill-filter__button"
                        )
                        .forEach(
                            function (filterButton) {

                                const isSelected =
                                    filterButton.dataset.group ===
                                    selectedGroup;

                                filterButton.classList.toggle(
                                    "is-active",
                                    isSelected
                                );

                                filterButton.setAttribute(
                                    "aria-pressed",
                                    String(isSelected)
                                );
                            }
                        );


                    // ----------------------------------
                    // HIỆN / ẨN KỸ NĂNG
                    // ----------------------------------

                    skills.forEach(
                        function (skill) {

                            const skillGroup =
                                skill.dataset.group;

                            const shouldShow =
                                selectedGroup === "all" ||
                                skillGroup === selectedGroup;


                            // Dùng classList để ẩn / hiện
                            skill.classList.toggle(
                                "is-hidden",
                                !shouldShow
                            );

                        }
                    );

                }
            );


            filterBox.appendChild(
                button
            );

        });


        // ------------------------------------------
        // Đưa bộ lọc lên trên danh sách kỹ năng
        // ------------------------------------------

        skillsSection.insertBefore(
            filterBox,
            skillsList
        );

    }


    // ==========================================
    // 2. NÚT SAO CHÉP EMAIL + TOOLTIP
    // ==========================================

    const copyEmailButton =
        document.querySelector("#copyEmail");

    const emailLink =
        document.querySelector(
            '#contact a[href^="mailto:"]'
        );


    if (copyEmailButton && emailLink) {

        // ------------------------------------------
        // Tạo tooltip bằng JavaScript
        // ------------------------------------------

        const tooltip =
            document.createElement("span");

        tooltip.classList.add(
            "email-tooltip"
        );

        tooltip.textContent =
            "Bấm để sao chép email";

        tooltip.setAttribute(
            "role",
            "status"
        );

        tooltip.setAttribute(
            "aria-live",
            "polite"
        );


        copyEmailButton.appendChild(
            tooltip
        );


        // ------------------------------------------
        // Hiện tooltip khi rê chuột
        // ------------------------------------------

        copyEmailButton.addEventListener(
            "mouseenter",
            function () {

                tooltip.textContent =
                    "Bấm để sao chép email";

                tooltip.classList.add(
                    "is-visible"
                );
            }
        );


        // ------------------------------------------
        // Ẩn tooltip khi rời chuột
        // ------------------------------------------

        copyEmailButton.addEventListener(
            "mouseleave",
            function () {

                tooltip.classList.remove(
                    "is-visible"
                );
            }
        );


        // ------------------------------------------
        // Sao chép email
        // ------------------------------------------

        copyEmailButton.addEventListener(
            "click",
            async function () {

                const email =
                    emailLink.textContent.trim();


                try {

                    await navigator.clipboard.writeText(
                        email
                    );


                    tooltip.textContent =
                        "Đã sao chép email!";

                    tooltip.classList.add(
                        "is-visible"
                    );


                    // Sau 2 giây tự ẩn tooltip
                    setTimeout(
                        function () {

                            tooltip.classList.remove(
                                "is-visible"
                            );

                        },
                        2000
                    );


                } catch (error) {

                    tooltip.textContent =
                        "Không thể sao chép email";

                    tooltip.classList.add(
                        "is-visible"
                    );

                }

            }
        );


        // ------------------------------------------
        // Hỗ trợ bàn phím
        // ------------------------------------------

        copyEmailButton.addEventListener(
            "focus",
            function () {

                tooltip.textContent =
                    "Nhấn Enter hoặc Space để sao chép";

                tooltip.classList.add(
                    "is-visible"
                );

            }
        );


        copyEmailButton.addEventListener(
            "blur",
            function () {

                tooltip.classList.remove(
                    "is-visible"
                );

            }
        );

    }

});