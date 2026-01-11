# 🚀 Quick Start Guide - CineCraze

Get your movie streaming platform up and running in 5 minutes!

## ⚡ Installation Steps

### 1. Upload Files
Upload all files to your web hosting root directory (public_html, www, or htdocs)

### 2. Run Installer
Open your browser and navigate to:
```
http://yourdomain.com/install/install.php
```

### 3. Configure Database
Enter your database details:
- **Host**: Usually `localhost`
- **Username**: Your MySQL username
- **Password**: Your MySQL password
- **Database**: Create a new database name (e.g., `cinecraze_db`)

### 4. Create Admin Account
- Choose a username
- Enter your email
- Set a strong password

### 5. Done! 🎉
- Delete the `/install` directory
- Login at: `http://yourdomain.com/admin/`
- Start adding movies!

## 📝 First Steps After Installation

1. **Login to Admin Panel**
   - Go to: `http://yourdomain.com/admin/`
   - Use the credentials you created

2. **Add Your First Movie**
   - Click "Add Content"
   - Fill in the details:
     - Title: Movie name
     - Type: Movie/Series/Live
     - Thumbnail: Image URL (e.g., from TMDB, IMDB)
     - Video URL: YouTube link or direct video URL
   - Click "Save Content"

3. **Test Your Site**
   - Visit: `http://yourdomain.com/`
   - Register as a user
   - Browse and watch your content

## 🎬 Adding Content

### YouTube Videos
Use any of these formats:
```
https://www.youtube.com/watch?v=VIDEO_ID
https://youtu.be/VIDEO_ID
```

### Direct Video Files
```
https://example.com/videos/movie.mp4
```

### Getting Thumbnails
Free image sources:
- TMDB: https://www.themoviedb.org/
- IMDB: https://www.imdb.com/
- Unsplash: https://unsplash.com/

## 🔧 Common Issues

### Database Connection Failed
- Double-check your database credentials
- Make sure the database exists
- Verify MySQL is running

### 500 Error
- Check file permissions (755 for folders, 644 for files)
- Review .htaccess compatibility
- Check PHP error logs

### Videos Not Playing
- Verify video URL is correct
- Check if video is publicly accessible
- Ensure Plyr.js CDN is loading

## 📱 Testing Responsive Design

Open your site on:
- Desktop browser (resize window)
- Mobile phone browser
- Tablet
- Use browser DevTools (F12) → Device Mode

## 🎨 Customizing

### Change Site Name
Edit `/config/config.php`:
```php
define('SITE_NAME', 'Your Site Name');
```

### Change Colors
Edit CSS variables in `index.php` and `admin/index.php`:
```css
--primary: #e50914;  /* Change this */
```

### Add Categories
Login to admin → Add movies with different categories
They'll appear automatically in the filter dropdown

## 📊 Recommended Hosting

### Free Hosting
- InfinityFree: https://infinityfree.net/
- 000webhost: https://www.000webhost.com/
- Byethost: https://byet.host/

### Paid Hosting (Starting at $2-5/month)
- Hostinger
- Bluehost
- SiteGround
- Namecheap

## 🔐 Security Checklist

After installation:
- [ ] Delete `/install` directory
- [ ] Change default admin password
- [ ] Enable HTTPS (SSL certificate)
- [ ] Set proper file permissions
- [ ] Disable PHP error display in production

## 🎯 Next Steps

1. Add more movies and series
2. Invite users to register
3. Monitor the dashboard stats
4. Customize the design
5. Add more categories
6. Enable HTTPS for security

## 💡 Pro Tips

### Batch Adding Content
Prepare a spreadsheet with:
- Movie titles
- Thumbnail URLs
- Video URLs
- Descriptions

Then add them one by one or create a bulk import script.

### Performance
- Use CDN for thumbnails
- Host videos on YouTube/Vimeo
- Enable caching in .htaccess
- Optimize database regularly

### Marketing
- Share on social media
- SEO optimize titles and descriptions
- Create movie collection lists
- Add user reviews (future feature)

## 📞 Need Help?

1. Read the full README.md
2. Check the troubleshooting section
3. Review PHP error logs
4. Verify all requirements are met

---

**Happy Streaming! 🎬🍿**

*Remember to delete the `/install` directory after setup for security!*
