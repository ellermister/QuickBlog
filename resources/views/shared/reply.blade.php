@if(1==2)
<div class="section-row">
    <div class="section-title">
        <h2>Leave a reply</h2>
        <p>your email address will not be published. required fields are marked *</p>
    </div>
    <form class="post-reply">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    <span>Name *</span>
                    <input class="input" type="text" name="name">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <span>Email *</span>
                    <input class="input" type="email" name="email">
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <span>Website</span>
                    <input class="input" type="text" name="website">
                </div>
            </div>
            <div class="col-md-12">
                <div class="form-group">
                    <textarea class="input" name="message" placeholder="Message"></textarea>
                </div>
                <button class="primary-button">Submit</button>
            </div>
        </div>
    </form>
</div>
@endif