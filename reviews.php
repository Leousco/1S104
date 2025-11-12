<!-- Reviews Section -->
<div class="review-box">
  <div class="score" id="avg-score">0.0</div>
  <div class="stars" id="avg-stars">☆☆☆☆☆</div>
  <h3>Reviews</h3>

  <!-- Ratings summary -->
  <div class="bar-container"><span class="bar-label">5★</span>
    <div class="bar"><div class="bar-fill" id="bar-5"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">4★</span>
    <div class="bar"><div class="bar-fill" id="bar-4"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">3★</span>
    <div class="bar"><div class="bar-fill" id="bar-3"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">2★</span>
    <div class="bar"><div class="bar-fill" id="bar-2"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">1★</span>
    <div class="bar"><div class="bar-fill" id="bar-1"></div></div>
  </div>

  <!-- Placeholder -->
  <p style="margin-top:15px; color:#666; font-size:0.9em;" id="review-placeholder">
    0 reviews submitted
  </p>

  <!-- Comment section -->
  <div id="review-list"></div>

  <!-- Review form -->
  <form id="review-form" style="margin-top:20px;">
    <input type="text" id="name" placeholder="Your name" required
      style="width:100%; padding:8px; margin-bottom:8px; border:1px solid #ccc; border-radius:5px;">
    
    <select id="rating" required
      style="width:100%; padding:8px; margin-bottom:8px; border:1px solid #ccc; border-radius:5px;">
      <option value="">Select rating</option>
      <option value="5">★★★★★ (5)</option>
      <option value="4">★★★★☆ (4)</option>
      <option value="3">★★★☆☆ (3)</option>
      <option value="2">★★☆☆☆ (2)</option>
      <option value="1">★☆☆☆☆ (1)</option>
    </select>

    <textarea id="comment" placeholder="Write your review..." required
      style="width:100%; padding:8px; margin-bottom:8px; border:1px solid #ccc; border-radius:5px;"></textarea>
    
    <button type="submit"
      style="width:100%; padding:10px; border:none; background:#4caf50; color:white; border-radius:5px; cursor:pointer;">
      Submit Review
    </button>
  </form>
</div>

<!-- Reviews Section -->
<div class="review-box">
  <div class="score" id="avg-score">0.0</div>
  <div class="stars" id="avg-stars">☆☆☆☆☆</div>
  <h3>Reviews</h3>

  <!-- Ratings summary -->
  <div class="bar-container"><span class="bar-label">5★</span>
    <div class="bar"><div class="bar-fill" id="bar-5"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">4★</span>
    <div class="bar"><div class="bar-fill" id="bar-4"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">3★</span>
    <div class="bar"><div class="bar-fill" id="bar-3"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">2★</span>
    <div class="bar"><div class="bar-fill" id="bar-2"></div></div>
  </div>
  <div class="bar-container"><span class="bar-label">1★</span>
    <div class="bar"><div class="bar-fill" id="bar-1"></div></div>
  </div>

  <!-- Placeholder -->
  <p style="margin-top:15px; color:#666; font-size:0.9em;" id="review-placeholder">
    0 reviews submitted
  </p>

  <!-- Comment section -->
  <div id="review-list"></div>

  <!-- Review form -->
  <form id="review-form" style="margin-top:20px;">
    <input type="text" id="name" placeholder="Your name" required
      style="width:100%; padding:8px; margin-bottom:8px; border:1px solid #ccc; border-radius:5px;">
    
    <select id="rating" required
      style="width:100%; padding:8px; margin-bottom:8px; border:1px solid #ccc; border-radius:5px;">
      <option value="">Select rating</option>
      <option value="5">★★★★★ (5)</option>
      <option value="4">★★★★☆ (4)</option>
      <option value="3">★★★☆☆ (3)</option>
      <option value="2">★★☆☆☆ (2)</option>
      <option value="1">★☆☆☆☆ (1)</option>
    </select>

    <textarea id="comment" placeholder="Write your review..." required
      style="width:100%; padding:8px; margin-bottom:8px; border:1px solid #ccc; border-radius:5px;"></textarea>
    
    <button type="submit"
      style="width:100%; padding:10px; border:none; background:#166943; color:white; border-radius:5px; cursor:pointer;">
      Submit Review
    </button>
  </form>
</div>

<style>
.review-box {
  max-width: 400px;
  margin: 20px auto;
  border: 1px solid #cce0cc;
  border-radius: 8px;
  padding: 15px;
  background: #f0fff0;
  font-family: Arial, sans-serif;
}
.review-box .score {
  font-size: 2em;
  font-weight: bold;
}
.review-box .stars {
  color: gold;
  font-size: 1.2em;
}
.bar-container {
  margin: 5px 0;
  display: flex;
  align-items: center;
}
.bar-label {
  width: 20px;
  font-size: 0.9em;
}
.bar {
  flex: 1;
  background: #eee;
  border-radius: 5px;
  overflow: hidden;
  margin-left: 5px;
}
.bar-fill {
  height: 12px;
  background: gold;
  width: 0%;
}
.review-item {
  display: flex;
  align-items: flex-start;
  background: #d9f2d9;
  border-radius: 6px;
  padding: 10px;
  margin-top: 10px;
}
.review-avatar {
  width: 40px;
  height: 40px;
  background: #4caf50;
  border-radius: 50%;
  margin-right: 10px;
}
.review-content {
  flex: 1;
}
.review-name {
  font-weight: bold;
}
.review-stars {
  color: gold;
  font-size: 0.9em;
}
.review-text {
  font-size: 0.9em;
  color: #333;
  margin-top: 4px;
}
</style>


<script>
let avgRating = 0; 
let totalReviews = 0;
let starDistribution = { 5: 0, 4: 0, 3: 0, 2: 0, 1: 0 };
let reviews = [];

function getStars(rating) {
  return "★".repeat(rating) + "☆".repeat(5 - rating);
}

function updateSummary() {
  document.getElementById("avg-score").textContent = avgRating.toFixed(1);
  document.getElementById("avg-stars").textContent = getStars(Math.round(avgRating));

  for (let i = 5; i >= 1; i--) {
    const percent = totalReviews ? (starDistribution[i] / totalReviews) * 100 : 0;
    document.getElementById("bar-" + i).style.width = percent + "%";
  }

  document.getElementById("review-placeholder").textContent = totalReviews + " reviews submitted";
}

function renderReviews() {
  const list = document.getElementById("review-list");
  list.innerHTML = "";

  reviews.forEach(r => {
    const item = document.createElement("div");
    item.className = "review-item";
    item.innerHTML = `
      <div class="review-avatar"></div>
      <div class="review-content">
        <div class="review-name">${r.name}</div>
        <div class="review-stars">${getStars(r.rating)}</div>
        <div class="review-text">${r.text}</div>
      </div>
    `;
    list.prepend(item); // newest first
  });
}

document.getElementById("review-form").addEventListener("submit", function(e) {
  e.preventDefault();
  
  const name = document.getElementById("name").value.trim();
  const rating = parseInt(document.getElementById("rating").value);
  const comment = document.getElementById("comment").value.trim();

  if (!name || !rating || !comment) return;

  const newReview = { name, rating, text: comment };
  reviews.push(newReview);

  totalReviews++;
  starDistribution[rating]++;
  avgRating = (avgRating * (totalReviews - 1) + rating) / totalReviews;

  updateSummary();
  renderReviews();

  // reset form
  this.reset();
});

updateSummary();
renderReviews();
</script>




