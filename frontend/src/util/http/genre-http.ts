import HttpResource from './http-resources';
import { httpVideo } from './index';

const genrehttp = new HttpResource(httpVideo, "genres");
export default genrehttp;