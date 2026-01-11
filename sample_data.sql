-- Sample Data for CineCraze
-- This file contains sample movies, series, and categories for testing
-- Run this after installation to populate your database with demo content

-- Insert sample movies
INSERT INTO movies (title, description, thumbnail, video_url, trailer_url, type, category, year, rating, duration, language, country, genre, cast_crew, featured, status, created_at) VALUES

('The Dark Knight', 'When the menace known as the Joker wreaks havoc and chaos on the people of Gotham, Batman must accept one of the greatest psychological and physical tests of his ability to fight injustice.', 'https://image.tmdb.org/t/p/w500/qJ2tW6WMUDux911r6m7haRef0WH.jpg', 'https://www.youtube.com/watch?v=EXeTwQWrcwY', 'https://www.youtube.com/watch?v=EXeTwQWrcwY', 'movie', 'action', 2008, 9.0, 152, 'English', 'USA', 'Action, Crime, Drama', 'Christian Bale, Heath Ledger, Aaron Eckhart', 1, 'active', NOW()),

('Inception', 'A thief who steals corporate secrets through the use of dream-sharing technology is given the inverse task of planting an idea into the mind of a C.E.O.', 'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg', 'https://www.youtube.com/watch?v=YoHD9XEInc0', 'https://www.youtube.com/watch?v=YoHD9XEInc0', 'movie', 'sci-fi', 2010, 8.8, 148, 'English', 'USA', 'Action, Sci-Fi, Thriller', 'Leonardo DiCaprio, Joseph Gordon-Levitt, Ellen Page', 1, 'active', NOW()),

('The Shawshank Redemption', 'Two imprisoned men bond over a number of years, finding solace and eventual redemption through acts of common decency.', 'https://image.tmdb.org/t/p/w500/q6y0Go1tsGEsmtFryDOJo3dEmqu.jpg', 'https://www.youtube.com/watch?v=6hB3S9bIaco', 'https://www.youtube.com/watch?v=6hB3S9bIaco', 'movie', 'drama', 1994, 9.3, 142, 'English', 'USA', 'Drama', 'Tim Robbins, Morgan Freeman', 1, 'active', NOW()),

('Pulp Fiction', 'The lives of two mob hitmen, a boxer, a gangster and his wife intertwine in four tales of violence and redemption.', 'https://image.tmdb.org/t/p/w500/d5iIlFn5s0ImszYzBPb8JPIfbXD.jpg', 'https://www.youtube.com/watch?v=s7EdQ4FqbhY', 'https://www.youtube.com/watch?v=s7EdQ4FqbhY', 'movie', 'thriller', 1994, 8.9, 154, 'English', 'USA', 'Crime, Drama', 'John Travolta, Uma Thurman, Samuel L. Jackson', 1, 'active', NOW()),

('Forrest Gump', 'The presidencies of Kennedy and Johnson, the Vietnam War, and other historical events unfold from the perspective of an Alabama man with an IQ of 75.', 'https://image.tmdb.org/t/p/w500/saHP97rTPS5eLmrLQEcANmKrsFl.jpg', 'https://www.youtube.com/watch?v=bLvqoHBptjg', 'https://www.youtube.com/watch?v=bLvqoHBptjg', 'movie', 'drama', 1994, 8.8, 142, 'English', 'USA', 'Drama, Romance', 'Tom Hanks, Robin Wright', 0, 'active', NOW()),

('The Matrix', 'A computer hacker learns from mysterious rebels about the true nature of his reality and his role in the war against its controllers.', 'https://image.tmdb.org/t/p/w500/f89U3ADr1oiB1s9GkdPOEpXUk5H.jpg', 'https://www.youtube.com/watch?v=vKQi3bBA1y8', 'https://www.youtube.com/watch?v=vKQi3bBA1y8', 'movie', 'sci-fi', 1999, 8.7, 136, 'English', 'USA', 'Action, Sci-Fi', 'Keanu Reeves, Laurence Fishburne, Carrie-Anne Moss', 0, 'active', NOW()),

('The Godfather', 'The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.', 'https://image.tmdb.org/t/p/w500/3bhkrj58Vtu7enYsRolD1fZdja1.jpg', 'https://www.youtube.com/watch?v=sY1S34973zA', 'https://www.youtube.com/watch?v=sY1S34973zA', 'movie', 'drama', 1972, 9.2, 175, 'English', 'USA', 'Crime, Drama', 'Marlon Brando, Al Pacino', 0, 'active', NOW()),

('Interstellar', 'A team of explorers travel through a wormhole in space in an attempt to ensure humanity survival.', 'https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg', 'https://www.youtube.com/watch?v=zSWdZVtXT7E', 'https://www.youtube.com/watch?v=zSWdZVtXT7E', 'movie', 'sci-fi', 2014, 8.6, 169, 'English', 'USA', 'Adventure, Drama, Sci-Fi', 'Matthew McConaughey, Anne Hathaway', 0, 'active', NOW()),

('The Conjuring', 'Paranormal investigators Ed and Lorraine Warren work to help a family terrorized by a dark presence in their farmhouse.', 'https://image.tmdb.org/t/p/w500/wVYREutTvI2tmxr6ujrHT704wGF.jpg', 'https://www.youtube.com/watch?v=k10ETZ41q5o', 'https://www.youtube.com/watch?v=k10ETZ41q5o', 'movie', 'horror', 2013, 7.5, 112, 'English', 'USA', 'Horror, Mystery, Thriller', 'Vera Farmiga, Patrick Wilson', 0, 'active', NOW()),

('La La Land', 'While navigating their careers in Los Angeles, a pianist and an actress fall in love while attempting to reconcile their aspirations for the future.', 'https://image.tmdb.org/t/p/w500/uDO8zWDhfWwoFdKS4fzkUJt0Rf0.jpg', 'https://www.youtube.com/watch?v=0pdqf4P9MB8', 'https://www.youtube.com/watch?v=0pdqf4P9MB8', 'movie', 'romance', 2016, 8.0, 128, 'English', 'USA', 'Comedy, Drama, Music, Romance', 'Ryan Gosling, Emma Stone', 0, 'active', NOW()),

-- Sample TV Series
('Breaking Bad', 'A high school chemistry teacher diagnosed with inoperable lung cancer turns to manufacturing and selling methamphetamine.', 'https://image.tmdb.org/t/p/w500/ggFHVNu6YYI5L9pCfOacjizRGt.jpg', 'https://www.youtube.com/watch?v=HhesaQXLuRY', 'https://www.youtube.com/watch?v=HhesaQXLuRY', 'series', 'thriller', 2008, 9.5, 47, 'English', 'USA', 'Crime, Drama, Thriller', 'Bryan Cranston, Aaron Paul', 0, 'active', NOW()),

('Game of Thrones', 'Nine noble families fight for control over the lands of Westeros, while an ancient enemy returns.', 'https://image.tmdb.org/t/p/w500/u3bZgnGQ9T01sWNhyveQz0wH0Hl.jpg', 'https://www.youtube.com/watch?v=KPLWWIOCOOQ', 'https://www.youtube.com/watch?v=KPLWWIOCOOQ', 'series', 'drama', 2011, 9.2, 57, 'English', 'USA', 'Action, Adventure, Drama', 'Emilia Clarke, Peter Dinklage, Kit Harington', 0, 'active', NOW()),

('Stranger Things', 'When a young boy disappears, his mother, a police chief and his friends must confront terrifying supernatural forces.', 'https://image.tmdb.org/t/p/w500/x2LSRK2Cm7MZhjluni1msVJ3wDF.jpg', 'https://www.youtube.com/watch?v=b9EkMc79ZSU', 'https://www.youtube.com/watch?v=b9EkMc79ZSU', 'series', 'sci-fi', 2016, 8.7, 51, 'English', 'USA', 'Drama, Fantasy, Horror', 'Millie Bobby Brown, Finn Wolfhard', 0, 'active', NOW()),

('The Crown', 'Follows the political rivalries and romance of Queen Elizabeth II reign and the events that shaped the second half of the 20th century.', 'https://image.tmdb.org/t/p/w500/1M876KPjulVwppEpldhdc8V4o68.jpg', 'https://www.youtube.com/watch?v=JWtnJjn6ng0', 'https://www.youtube.com/watch?v=JWtnJjn6ng0', 'series', 'drama', 2016, 8.6, 58, 'English', 'UK', 'Drama, History', 'Claire Foy, Olivia Colman, Imelda Staunton', 0, 'active', NOW()),

('The Office', 'A mockumentary on a group of typical office workers, where the workday consists of ego clashes and inappropriate behavior.', 'https://image.tmdb.org/t/p/w500/qWnJzyZhyy74gjpSjIXWmuk0ifX.jpg', 'https://www.youtube.com/watch?v=LHOtME2DL4g', 'https://www.youtube.com/watch?v=LHOtME2DL4g', 'series', 'comedy', 2005, 9.0, 22, 'English', 'USA', 'Comedy', 'Steve Carell, Rainn Wilson, John Krasinski', 0, 'active', NOW()),

-- Live TV Samples (Placeholder URLs - replace with actual streams)
('CNN Live', 'Breaking news and live coverage from CNN', 'https://upload.wikimedia.org/wikipedia/commons/thumb/b/b1/CNN.svg/300px-CNN.svg.png', 'https://www.youtube.com/watch?v=live_stream_id', NULL, 'live', 'documentary', 2024, 0, 0, 'English', 'USA', 'News', 'Various', 0, 'active', NOW()),

('Sports Channel', '24/7 Sports coverage and highlights', 'https://via.placeholder.com/500x750/FF0000/FFFFFF?text=Sports', 'https://www.youtube.com/watch?v=live_stream_id', NULL, 'live', 'action', 2024, 0, 0, 'English', 'USA', 'Sports', 'Various', 0, 'active', NOW());

-- Note: For live TV, you'll need to replace the video_url with actual live stream URLs
-- These can be from:
-- - YouTube Live streams
-- - Direct HLS/DASH stream URLs
-- - Embedded streaming services

-- Update featured flag for random movies (optional)
UPDATE movies SET featured = 1 WHERE id IN (1, 2, 3, 4, 5) LIMIT 5;
