<script>
  function toggleMenu() {
    document.getElementById("profileMenu").classList.toggle("active");
  }

  document.addEventListener("click", function(e) {
    const menu = document.getElementById("profileMenu");
    const avatar = document.querySelector(".avatar");

    if (!avatar.contains(e.target) && !menu.contains(e.target)) {
      menu.classList.remove("active");
    }
  });

  function updateDateTime() {
  const now = new Date();

  const month = now.toLocaleString('default', { month: 'long' });
  const day = now.getDate();
  const year = now.getFullYear();

  let hours = now.getHours();
  let minutes = now.getMinutes();
  let ampm = hours >= 12 ? 'PM' : 'AM';

  hours = hours % 12;
  hours = hours ? hours : 12; // 0 becomes 12
  minutes = minutes < 10 ? '0' + minutes : minutes;

  const time = hours + ":" + minutes + " " + ampm;

  document.getElementById("month").textContent = month;
  document.getElementById("day").textContent = day;
  document.getElementById("year").textContent = year;
  document.getElementById("time").textContent = time;
}
updateDateTime();
setInterval(updateDateTime, 1000);
</script>